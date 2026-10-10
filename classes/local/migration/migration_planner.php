<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Reviewed immutable family migration planning.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\policy\author_policy;
use local_filebank\local\logging\log;

/**
 * Creates explicit file decisions without writing course materials.
 */
final class migration_planner {
    /**
     * Preview copies between two values of a configured course dimension.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param bool $layoutconfirmed Reviewed section layout.
     * @param string $scopekey Configured capture filter or edition.
     * @param array $options Migration-owned rules and capture filters.
     * @return \stdClass Stored plan.
     */
    public static function preview(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        bool $layoutconfirmed = false,
        string $scopekey = 'edition',
        array $options = []
    ): \stdClass {
        global $DB, $USER;
        require_capability('local/filebank:planmigration', \context_system::instance());
        [$profile, $revision, $rules] = profiles::load($profileid);
        $scopecapture = $scopekey !== '' ? course_scope::capture($rules, $scopekey) : '';
        $config = $options['migration'] ?? $rules->get_config()['migration'] ?? [];
        $placements = $options['placements'] ?? [];
        $excluded = $options['excluded'] ?? [];
        $ignoredissues = $options['ignoredissues'] ?? [];
        $filechoices = $options['filechoices'] ?? [];
        $filters = $options['filters'] ?? null;
        if ($filters !== null) {
            foreach (['source', 'target'] as $side) {
                if (!is_array($filters[$side] ?? null) || array_diff_key($filters[$side], $rules->filter_definitions())) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                foreach ($filters[$side] as $value) {
                    if (!is_string($value) || strlen($value) > 255) {
                        throw new \moodle_exception('invalidprofile', 'local_filebank');
                    }
                }
                $filters[$side] = array_filter($filters[$side], static fn($value) => $value !== '');
            }
        }
        if (
            ($filters === null && ($sourceedition === '' || $targetedition === '' || $sourceedition === $targetedition))
                || (($config['section_match']['strategy'] ?? 'ordinal') === 'ordinal' && !$layoutconfirmed)
        ) {
            throw new \moodle_exception('layoutrequired', 'local_filebank');
        }
        $pairs = [];
        $members = $DB->get_records('local_filebank_course', ['revisionid' => $revision->id], 'courseid');
        foreach ($members as $member) {
            $course = $DB->get_record('course', ['id' => $member->courseid]);
            if (!$course || !($identity = $rules->resolve($course))) {
                continue;
            }
            $dimensions = $identity['dimensions'];
            unset($dimensions[$scopecapture]);
            foreach (array_keys(($filters['source'] ?? []) + ($filters['target'] ?? [])) as $key) {
                unset($dimensions[course_scope::capture($rules, $key)]);
            }
            $key = $identity['familyhash'] . ':' . hash('sha256', json_encode($dimensions));
            if ($filters !== null && !$rules->filter_definitions()) {
                $pairs['course:' . $course->id] = ['source' => [$course->id], 'target' => [$course->id]];
                continue;
            }
            $value = $scopekey === 'edition' ? $identity['edition'] : ($identity['filters'][$scopekey] ?? null);
            $source = $filters === null ? $value === $sourceedition
                : !array_diff_assoc($filters['source'], $identity['filters']);
            $target = $filters === null ? $value === $targetedition
                : !array_diff_assoc($filters['target'], $identity['filters']);
            if ($source) {
                $pairs[$key]['source'][] = $course->id;
            }
            if ($target) {
                $pairs[$key]['target'][] = $course->id;
            }
        }
        $operations = [];
        $issues = [];
        $creations = [];
        foreach ($pairs as $pair) {
            if (empty($pair['source'])) {
                continue;
            }
            if (count($pair['source']) !== 1 || count($pair['target'] ?? []) !== 1) {
                $issues[] = ['reason' => 'ambiguous_course_pair', 'courses' => $pair];
                continue;
            }
            $sourcecourse = $pair['source'][0];
            $targetcourse = $pair['target'][0];
            require_capability('moodle/course:manageactivities', \context_course::instance($targetcourse));
            $sourcescope = [];
            $sourcecourseinfo = get_course($sourcecourse);
            $targetcourseinfo = get_course($targetcourse);
            $sourceidentity = $rules->resolve($sourcecourseinfo);
            $targetidentity = $rules->resolve($targetcourseinfo);
            foreach ($config['correspondences'] ?? [$config] as $correspondence) {
                $matched = false;
                foreach (get_fast_modinfo($sourcecourse)->get_cms() as $sourcecm) {
                    $captures = placement::match($sourcecm, $correspondence['source_module'] ?? []);
                    if ($captures === null) {
                        continue;
                    }
                    $matched = true;
                    $sourcesection = get_fast_modinfo($sourcecourse)->get_section_info($sourcecm->sectionnum);
                    if (
                        !$sourcecourseinfo->visible || !$sourcecm->visible || !empty($sourcecm->availability)
                        || !$sourcesection->visible || !empty($sourcesection->availability)
                    ) {
                        continue;
                    }
                    $placementkey = $sourcecm->id . ':' . $targetcourse;
                    $override = $placements[$placementkey] ?? [];
                    if (!empty($override['exclude'])) {
                        continue;
                    }
                    $captures += ['name' => $sourcecm->name, 'idnumber' => $sourcecm->idnumber];
                    try {
                        // Authorize public leaves before producing destination errors or creation requests.
                        $provider = \local_filebank\local\catalog\source_registry::provider($sourcecm->modname);
                        $files = [];
                        foreach ($provider ? $provider->files($sourcecm) : [] as $candidate) {
                            if (
                                \local_filebank\local\catalog\source_registry::enabled(
                                    $rules->get_config()['publication']['allowed_sources']
                                    ?? ['mod_folder/content', 'mod_resource/content'],
                                    $candidate
                                )
                            ) {
                                $files[] = $candidate;
                            }
                        }
                        if (!$files) {
                            continue;
                        }
                        foreach ($files as $candidate) {
                            material_source::resolve(
                                $candidate->get_pathnamehash(),
                                $targetcourse,
                                $profileid,
                                [$profile, $revision, $rules],
                                $sourcescope
                            );
                        }
                        $sectionid = !empty($override['sectionid']) ? (int)$override['sectionid']
                            : placement::section($sourcecm, $targetcourse, $config['section_match'] ?? []);
                        $DB->get_record('course_sections', ['id' => $sectionid, 'course' => $targetcourse], '*', MUST_EXIST);
                        $targets = [];
                        foreach (get_fast_modinfo($targetcourse)->get_cms() as $targetcm) {
                            if (
                                (!empty($override['cmid']) ? $targetcm->id == $override['cmid']
                                    : (!$override && $targetcm->section == $sectionid
                                        && placement::match($targetcm, $correspondence['target_module'] ?? [], $captures) !== null))
                            ) {
                                $targets[] = $targetcm;
                            }
                        }
                        if (
                            count($targets) > 1 || ($targets && $targets[0]->modname !== 'folder')
                            || (!$targets && empty($override['create']) && ($config['missing_target'] ?? 'block') !== 'create')
                        ) {
                            throw new \moodle_exception('placementambiguous', 'local_filebank');
                        }
                        if (!$targets) {
                            $targetdata = target_binding::descriptor(
                                $targetcourse,
                                $sectionid,
                                !empty($override['create']) ? ['field' => 'name', 'equals' => $sourcecm->name]
                                    : $correspondence['target_module'],
                                $captures
                            );
                            foreach (get_fast_modinfo($targetcourse)->get_cms() as $existingcm) {
                                if (
                                    $existingcm->modname === 'folder' && $existingcm->section == $sectionid
                                    && ($targetdata['idnumber'] !== '' ? $existingcm->idnumber === $targetdata['idnumber']
                                        : $existingcm->name === $targetdata['name'])
                                ) {
                                    throw new \moodle_exception('placementambiguous', 'local_filebank');
                                }
                            }
                            $targetkey = hash('sha256', json_encode($targetdata));
                            $creations[$targetkey] = $targetdata;
                            $target = (object)['id' => 0, 'context' => (object)['id' => 0]];
                        } else {
                            $target = $targets[0];
                            $targetdata = placement::snapshot($target->id);
                            $targetkey = '';
                        }
                        $targetcontent = [];
                        foreach (
                            get_file_storage()->get_area_files(
                                $target->context->id,
                                'mod_folder',
                                'content',
                                0,
                                'id',
                                false
                            ) as $targetfile
                        ) {
                            $targetcontent[$targetfile->get_contenthash()][] = $targetfile;
                        }
                        foreach ($files as $candidate) {
                            [$file, $cm] = material_source::resolve(
                                $candidate->get_pathnamehash(),
                                $targetcourse,
                                $profileid,
                                [$profile, $revision, $rules],
                                $sourcescope
                            );
                            $source = copy_decision::snapshot($file);
                            $existing = get_file_storage()->get_file(
                                $target->context->id,
                                'mod_folder',
                                'content',
                                0,
                                $file->get_filepath(),
                                $file->get_filename()
                            );
                            $existingdata = $existing ? copy_decision::snapshot($existing) : null;
                            $decision = copy_decision::classify($source, $existingdata);
                            $duplicate = null;
                            if ($decision === 'blocked_content' && ($config['same_path_different_content'] ?? '') === 'overwrite') {
                                    $decision = 'overwrite';
                            } else if ($decision === 'blocked_attribution' && ($config['identical_content'] ?? '') === 'skip') {
                                $decision = 'skip_content';
                                $duplicate = $existingdata;
                            }
                            if (
                                $decision === 'copy'
                                && ($config['same_content_different_path'] ?? 'skip-and-report') !== 'keep'
                            ) {
                                foreach ($targetcontent[$file->get_contenthash()] ?? [] as $targetfile) {
                                    $duplicate = copy_decision::snapshot($targetfile);
                                    $decision = (($config['identical_content'] ?? '') === 'skip'
                                        || copy_decision::classify($source, $duplicate) === 'skip_identical')
                                    ? 'skip_content' : 'blocked_attribution';
                                    if ($decision === 'blocked_attribution') {
                                        break;
                                    }
                                }
                            }
                            if ($decision === 'copy' || $decision === 'overwrite') {
                                try {
                                    author_policy::require_native_copy($file, $cm->course, $targetcourse);
                                } catch (\moodle_exception $exception) {
                                    $decision = 'blocked_policy';
                                }
                            }
                            $snapshot = ['source' => $source, 'sourcecourse' => $sourcecourse,
                            'sourceshortname' => $sourcecourseinfo->shortname,
                            'targetshortname' => $targetcourseinfo->shortname,
                            'target' => $targetdata, 'targetkey' => $targetkey, 'sourcecmid' => $sourcecm->id,
                            'sourcefoldername' => $sourcecm->name, 'existing' => $existingdata,
                            'duplicate' => $duplicate,
                            'sourceidentity' => $sourceidentity,
                            'targetidentity' => $targetidentity];
                            $opkey = hash('sha256', $file->get_pathnamehash() . ':' . ($targetkey ?: $target->id));
                            $choicekey = $file->get_pathnamehash() . ':' . $targetcourse;
                            if (($filechoices[$choicekey] ?? '') === 'overwrite' && $decision === 'blocked_content') {
                                author_policy::require_native_copy($file, $cm->course, $targetcourse);
                                $decision = 'overwrite';
                            }
                            $operations[$opkey] = ['sourcehash' => $file->get_pathnamehash(), 'targetcmid' => $target->id,
                            'decision' => $decision, 'snapshot' => $snapshot,
                            'included' => !in_array($choicekey, $excluded, true), 'opkey' => $opkey];
                        }
                    } catch (\moodle_exception $exception) {
                        $issues[] = ['sourcecmid' => $sourcecm->id, 'sourcecourseid' => $sourcecourse,
                        'targetcourseid' => $targetcourse,
                        'reason' => $exception->errorcode];
                    }
                }
                if (!$matched) {
                    $issues[] = ['reason' => 'sourcefoldermissing', 'sourcecourseid' => $sourcecourse, 'blocking' => false];
                }
            }
        }
        $issues = array_values(array_filter($issues, static fn($issue) => !in_array(
            hash('sha256', json_encode($issue)),
            $ignoredissues,
            true
        )));
        $document = ['sourceedition' => $sourceedition, 'targetedition' => $targetedition, 'scopekey' => $scopekey,
            'layoutconfirmed' => $layoutconfirmed, 'migration' => $config, 'filters' => $filters,
            'placements' => $placements, 'excluded' => $excluded, 'ignoredissues' => $ignoredissues,
            'filechoices' => $filechoices,
            'issues' => $issues, 'operationcount' => count($operations)];
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $blockedissues = array_filter($issues, static fn($issue) => $issue['blocking'] ?? true);
        $blockedfiles = array_filter($operations, static fn($operation) => $operation['included']
            && str_starts_with($operation['decision'], 'blocked_'));
        $blocked = $blockedissues || $blockedfiles;
        $haswork = array_filter($operations, static fn($op) => $op['included']
            && in_array($op['decision'], ['copy', 'overwrite'], true));
        $transaction = $DB->start_delegated_transaction();
        $planid = $DB->insert_record('local_filebank_plan', (object)[
            'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revision->id,
            'userid' => $USER->id, 'configjson' => $json, 'digest' => hash('sha256', $json),
            'state' => $blocked ? 'blocked' : ($haswork ? 'reviewable' : 'cancelled'), 'timecreated' => time(),
        ]);
        foreach ($creations as $key => $definition) {
            $DB->insert_record('local_filebank_target', (object)['planid' => $planid, 'targetkey' => $key,
                'definitionjson' => json_encode($definition), 'cmid' => 0, 'snapshotjson' => '{}']);
        }
        foreach ($operations as $operation) {
            $DB->insert_record('local_filebank_operation', (object)[
                'planid' => $planid, 'opkey' => $operation['opkey'], 'sourcehash' => $operation['sourcehash'],
                'targetcmid' => $operation['targetcmid'], 'sourcecourseid' => $operation['snapshot']['sourcecourse'],
                'targetcourseid' => $operation['snapshot']['target']['courseid'],
                'sourcecmid' => $operation['snapshot']['sourcecmid'], 'snapshot' => json_encode($operation['snapshot']),
                'included' => $operation['included'] ? 1 : 0,
                'decision' => $operation['decision'], 'state' => 'pending', 'result' => '{}', 'timemodified' => time(),
            ]);
        }
        $document['operationsdigest'] = plan_selection::manifest_digest($planid);
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $DB->update_record('local_filebank_plan', (object)[
            'id' => $planid, 'configjson' => $json, 'digest' => hash('sha256', $json),
        ]);
        $transaction->allow_commit();
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
        log::record('migration_previewed', $plan->uuid, ['operations' => count($operations), 'blocked' => (bool)$blocked]);
        return $plan;
    }
}
