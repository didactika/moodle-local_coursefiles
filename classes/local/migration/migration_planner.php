<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local\migration;

use local_coursebridge\local\catalog\material_source;
use local_coursebridge\local\profile\profiles;
use local_coursebridge\local\policy\author_policy;
use local_coursebridge\local\logging\log;

/**
 * Creates explicit file decisions without writing course materials.
 */
final class migration_planner {
    /**
     * Preview copies between two configured edition labels.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param bool $layoutconfirmed Reviewed section layout.
     * @return \stdClass Stored plan.
     */
    public static function preview(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        bool $layoutconfirmed = false
    ): \stdClass {
        global $DB, $USER;
        require_capability('local/coursebridge:planmigration', \context_system::instance());
        [$profile, $revision, $rules] = profiles::load($profileid);
        $config = $rules->get_config()['migration'] ?? [];
        if (
            $sourceedition === '' || $targetedition === '' || $sourceedition === $targetedition
                || (($config['section_match']['strategy'] ?? 'ordinal') === 'ordinal' && !$layoutconfirmed)
        ) {
            throw new \moodle_exception('layoutrequired', 'local_coursebridge');
        }
        $pairs = [];
        $members = $DB->get_records('local_coursebridge_course', ['revisionid' => $revision->id], 'courseid');
        foreach ($members as $member) {
            $course = $DB->get_record('course', ['id' => $member->courseid]);
            if (!$course || !($identity = $rules->resolve($course))) {
                continue;
            }
            $key = $identity['familyhash'] . ':' . hash('sha256', json_encode($identity['dimensions']));
            if ($identity['edition'] === $sourceedition) {
                $pairs[$key]['source'][] = $course->id;
            } else if ($identity['edition'] === $targetedition) {
                $pairs[$key]['target'][] = $course->id;
            }
        }
        $operations = [];
        $issues = [];
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
            foreach (get_fast_modinfo($sourcecourse)->get_cms() as $sourcecm) {
                $captures = placement::match($sourcecm, $config['source_module'] ?? []);
                if ($captures === null) {
                    continue;
                }
                try {
                    $sectionid = placement::section($sourcecm, $targetcourse, $config['section_match'] ?? []);
                    $targets = [];
                    foreach (get_fast_modinfo($targetcourse)->get_cms() as $targetcm) {
                        if (
                            $targetcm->section == $sectionid
                                && placement::match($targetcm, $config['target_module'] ?? [], $captures) !== null
                        ) {
                            $targets[] = $targetcm;
                        }
                    }
                    if (count($targets) !== 1 || $sourcecm->modname !== 'folder' || $targets[0]->modname !== 'folder') {
                        throw new \moodle_exception('placementambiguous', 'local_coursebridge');
                    }
                    $target = $targets[0];
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
                    $files = get_file_storage()->get_area_files(
                        $sourcecm->context->id,
                        'mod_folder',
                        'content',
                        0,
                        'filepath, filename',
                        false
                    );
                    foreach ($files as $candidate) {
                        [$file, $cm] = material_source::resolve($candidate->get_pathnamehash(), $targetcourse, $profileid);
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
                        if (
                            $decision === 'copy'
                                && ($config['same_content_different_path'] ?? 'skip-and-report') !== 'keep'
                        ) {
                            foreach ($targetcontent[$file->get_contenthash()] ?? [] as $targetfile) {
                                $duplicate = copy_decision::snapshot($targetfile);
                                $decision = copy_decision::classify($source, $duplicate) === 'skip_identical'
                                    ? 'skip_content' : 'blocked_attribution';
                                if ($decision === 'blocked_attribution') {
                                    break;
                                }
                            }
                        }
                        if ($decision === 'copy') {
                            try {
                                author_policy::require_native_copy($file, $cm->course, $targetcourse);
                            } catch (\moodle_exception $exception) {
                                $decision = 'blocked_policy';
                            }
                        }
                        $snapshot = ['source' => $source, 'sourcecourse' => $sourcecourse,
                            'target' => placement::snapshot($target->id), 'existing' => $existingdata,
                            'duplicate' => $duplicate,
                            'sourceidentity' => $rules->resolve(get_course($sourcecourse)),
                            'targetidentity' => $rules->resolve(get_course($targetcourse))];
                        $operations[] = ['sourcehash' => $file->get_pathnamehash(), 'targetcmid' => $target->id,
                            'decision' => $decision, 'snapshot' => $snapshot,
                            'opkey' => hash('sha256', $file->get_pathnamehash() . ':' . $target->id)];
                    }
                } catch (\moodle_exception $exception) {
                    $issues[] = ['sourcecmid' => $sourcecm->id, 'targetcourseid' => $targetcourse,
                        'reason' => $exception->errorcode];
                }
            }
        }
        $document = ['sourceedition' => $sourceedition, 'targetedition' => $targetedition,
            'layoutconfirmed' => $layoutconfirmed, 'issues' => $issues, 'operations' => $operations];
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $blocked = $issues || array_filter($operations, static fn($operation) =>
            strpos($operation['decision'], 'blocked_') === 0);
        $transaction = $DB->start_delegated_transaction();
        $planid = $DB->insert_record('local_coursebridge_plan', (object)[
            'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revision->id,
            'userid' => $USER->id, 'configjson' => $json, 'digest' => hash('sha256', $json),
            'state' => $blocked ? 'blocked' : 'reviewable', 'timecreated' => time(),
        ]);
        foreach ($operations as $operation) {
            $DB->insert_record('local_coursebridge_operation', (object)[
                'planid' => $planid, 'opkey' => $operation['opkey'], 'sourcehash' => $operation['sourcehash'],
                'targetcmid' => $operation['targetcmid'], 'snapshot' => json_encode($operation['snapshot']),
                'decision' => $operation['decision'], 'state' => 'pending', 'result' => '{}', 'timemodified' => time(),
            ]);
        }
        $transaction->allow_commit();
        $plan = $DB->get_record('local_coursebridge_plan', ['id' => $planid], '*', MUST_EXIST);
        log::record('migration_previewed', $plan->uuid, ['operations' => count($operations), 'blocked' => (bool)$blocked]);
        return $plan;
    }
}
