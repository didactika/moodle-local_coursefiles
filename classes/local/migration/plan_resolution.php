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

namespace local_filebank\local\migration;

/**
 * Explicit conflict choices create a new reviewed plan and retain the original history.
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plan_resolution {
    /**
     * Resolve one displayed issue or file under the queue/selection lock.
     * @param int $planid Original plan.
     * @param string $digest Current approval digest.
     * @param string $kind issue or file.
     * @param int $id Issue index or operation ID.
     * @param string $choice Explicit destination or exclusion choice.
     * @return int New plan, without writing any native material.
     */
    public static function apply(int $planid, string $digest, string $kind, int $id, string $choice): int {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        try {
            $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
            if (
                !hash_equals($plan->digest, $digest) || !hash_equals($digest, hash('sha256', $plan->configjson))
                || $DB->record_exists('local_filebank_run', ['planid' => $planid])
            ) {
                throw new \moodle_exception('planstale', 'local_filebank');
            }
            [, $revision] = \local_filebank\local\profile\profiles::load($plan->profileid);
            if ((int)$revision->id !== (int)$plan->revisionid) {
                throw new \moodle_exception('planstale', 'local_filebank');
            }
            $document = json_decode($plan->configjson, true);
            if (
                !hash_equals($document['operationsdigest'] ?? '', plan_selection::manifest_digest($planid))
                || (isset($document['selectiondigest'])
                    && !hash_equals($document['selectiondigest'], plan_selection::digest($planid)))
            ) {
                throw new \moodle_exception('planstale', 'local_filebank');
            }
            if ($kind === 'issue') {
                $issue = $document['issues'][$id] ?? null;
                if (!$issue || !($issue['blocking'] ?? true)) {
                    throw new \invalid_parameter_exception('Invalid migration issue');
                }
                if ($choice === 'exclude') {
                    if (!empty($issue['sourcecmid']) && !empty($issue['targetcourseid'])) {
                        $document['placements'][$issue['sourcecmid'] . ':' . $issue['targetcourseid']] = ['exclude' => true];
                    } else {
                        $document['ignoredissues'][] = hash('sha256', json_encode($issue));
                    }
                } else {
                    if (
                        !in_array($issue['reason'], ['placementambiguous', 'creationnamerequired'], true)
                        || empty($issue['sourcecmid']) || empty($issue['targetcourseid'])
                        || !preg_match('/^(folder|create):([1-9][0-9]*)$/', $choice, $matches)
                    ) {
                        throw new \invalid_parameter_exception('Invalid destination resolution');
                    }
                    $courseid = (int)$issue['targetcourseid'];
                    require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
                    if ($matches[1] === 'folder') {
                        $cm = get_coursemodule_from_id('folder', (int)$matches[2], $courseid, false, MUST_EXIST);
                        $override = ['cmid' => (int)$cm->id, 'sectionid' => (int)$cm->section];
                    } else {
                        require_capability('mod/folder:addinstance', \context_course::instance($courseid));
                        $section = $DB->get_record(
                            'course_sections',
                            ['id' => (int)$matches[2], 'course' => $courseid],
                            '*',
                            MUST_EXIST
                        );
                        $override = ['create' => true, 'sectionid' => (int)$section->id];
                    }
                    $document['placements'][$issue['sourcecmid'] . ':' . $courseid] = $override;
                }
            } else if ($kind === 'file') {
                $operation = $DB->get_record('local_filebank_operation', ['id' => $id, 'planid' => $planid], '*', MUST_EXIST);
                if ($choice !== 'overwrite' || $operation->decision !== 'blocked_content') {
                    throw new \invalid_parameter_exception('Invalid file conflict resolution');
                }
                $document['filechoices'][$operation->sourcehash . ':' . $operation->targetcourseid] = 'overwrite';
            } else {
                throw new \invalid_parameter_exception('Unknown conflict kind');
            }
            $excluded = array_fill_keys($document['excluded'] ?? [], true);
            $records = $DB->get_recordset(
                'local_filebank_operation',
                ['planid' => $planid],
                'id',
                'id, sourcehash, targetcourseid, included'
            );
            foreach ($records as $record) {
                $key = $record->sourcehash . ':' . $record->targetcourseid;
                if ($record->included) {
                    unset($excluded[$key]);
                } else {
                    $excluded[$key] = true;
                }
            }
            $records->close();
            $transaction = $DB->start_delegated_transaction();
            $newplan = migration_planner::preview(
                $plan->profileid,
                $document['sourceedition'],
                $document['targetedition'],
                !empty($document['layoutconfirmed']),
                $document['scopekey'] ?? 'edition',
                [
                    'migration' => $document['migration'], 'filters' => $document['filters'] ?? null,
                    'placements' => $document['placements'] ?? [], 'excluded' => array_keys($excluded),
                    'filechoices' => $document['filechoices'] ?? [], 'ignoredissues' => $document['ignoredissues'] ?? [],
                ]
            );
            \local_filebank\local\logging\log::record(
                'migration_conflict_resolved',
                $newplan->uuid,
                ['previousplan' => $planid, 'kind' => $kind, 'choice' => $choice]
            );
            $transaction->allow_commit();
            return (int)$newplan->id;
        } finally {
            $lock->release();
        }
    }
}
