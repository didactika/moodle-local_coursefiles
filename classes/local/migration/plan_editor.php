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
 * Changes inside a migration that has not run.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration;

use local_filebank\local\logging\log;
use local_filebank\local\profile\profiles;

/**
 * Settles what needs a decision, one by one or all of a kind at once, and changes the parameters of a migration, always inside the
 * same migration: the operations are worked out again and stored where they were, so no other migration is created.
 */
final class plan_editor {
    /** @var string[] What can be chosen for a file that is in conflict with what the destination already has. */
    public const FILE_CHOICES = ['overwrite', 'skip'];

    /** @var string The change has to be made on a migration that is of the current revision of the profile. */
    private const KEEP = 'keep';

    /** @var string The change works the migration out again with the current revision of the profile. */
    private const REBASE = 'rebase';

    /**
     * Settle some of the open decisions of a migration.
     *
     * @param int $planid The migration.
     * @param string $digest What the person is looking at.
     * @param string $kind file or issue.
     * @param int[] $ids Operations or positions in the list of issues; empty to settle every one of a nature.
     * @param string $choice overwrite or skip for a file; exclude, folder:ID or create:ID for an issue.
     * @param string $nature The nature to settle when no ids are given: a decision (blocked_content...) or an issue reason.
     * @return int How many were settled.
     */
    public static function resolve(
        int $planid,
        string $digest,
        string $kind,
        array $ids,
        string $choice,
        string $nature = ''
    ): int {
        return self::edit($planid, $digest, static function (array &$document) use ($planid, $kind, $ids, $choice, $nature): int {
            if ($kind === 'file') {
                return self::files($planid, $document, $ids, $choice, $nature);
            }
            if ($kind === 'issue') {
                return self::issues($document, $ids, $choice, $nature);
            }
            throw new \invalid_parameter_exception('Unknown decision kind');
        }, 'migration_conflict_resolved', ['kind' => $kind, 'choice' => $choice]);
    }

    /**
     * Change the parameters of a migration: the courses it goes between, the correspondences and the policies.
     *
     * @param int $planid The migration.
     * @param string $digest What the person is looking at.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param string $scopekey Filter dimension used for the edition-like labels.
     * @param array $options New filters, migration configuration and placements; what is not given is kept.
     */
    public static function reconfigure(
        int $planid,
        string $digest,
        string $sourceedition,
        string $targetedition,
        string $scopekey,
        array $options
    ): void {
        $change = static function (array &$document) use ($sourceedition, $targetedition, $scopekey, $options): int {
            $document['sourceedition'] = $sourceedition;
            $document['targetedition'] = $targetedition;
            $document['scopekey'] = $scopekey;
            foreach (['migration', 'filters', 'placements'] as $key) {
                if (array_key_exists($key, $options)) {
                    $document[$key] = $options[$key];
                }
            }
            return 0;
        };
        self::edit($planid, $digest, $change, 'migration_reconfigured', [], self::REBASE);
    }

    /**
     * Run a change under the lock of the migration and store the result where the migration is.
     *
     * @param int $planid The migration.
     * @param string $digest What the person is looking at.
     * @param callable $change Receives the document of the migration and returns how many things it changed.
     * @param string $action What to write in the log.
     * @param array $details Extra data for the log.
     * @param string $revision KEEP to refuse a migration that was worked out with an older revision of the profile, or REBASE to
     *     work it out again with the current one.
     * @return int What the change returned.
     */
    private static function edit(
        int $planid,
        string $digest,
        callable $change,
        string $action,
        array $details,
        string $revision = self::KEEP
    ): int {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        try {
            $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
            self::require_current($plan, $digest, $revision === self::KEEP);
            $document = json_decode($plan->configjson, true);
            $document['excluded'] = self::excluded($planid, $document);
            $changed = $change($document);
            $transaction = $DB->start_delegated_transaction();
            $stored = migration_planner::replace($planid, $plan->profileid, $document);
            log::record($action, $stored->uuid, ['changed' => $changed] + $details);
            $transaction->allow_commit();
            return $changed;
        } finally {
            $lock->release();
        }
    }

    /**
     * Refuse a change to something that was edited elsewhere, ran, or belongs to an older profile revision.
     *
     * @param \stdClass $plan The migration.
     * @param string $digest What the person is looking at.
     * @param bool $checkrevision Whether the migration has to be of the current revision of the profile.
     */
    private static function require_current(\stdClass $plan, string $digest, bool $checkrevision): void {
        global $DB;
        if (
            !hash_equals($plan->digest, $digest) || !hash_equals($digest, hash('sha256', $plan->configjson))
            || $DB->record_exists('local_filebank_run', ['planid' => $plan->id])
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        [, $revision] = profiles::load($plan->profileid);
        if ($checkrevision && (int) $revision->id !== (int) $plan->revisionid) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        $document = json_decode($plan->configjson, true);
        if (
            !hash_equals($document['operationsdigest'] ?? '', plan_selection::manifest_digest($plan->id))
            || (isset($document['selectiondigest'])
                && !hash_equals($document['selectiondigest'], plan_selection::digest($plan->id)))
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
    }

    /**
     * Settle files that are in conflict.
     *
     * @param int $planid The migration.
     * @param array $document Document of the migration.
     * @param int[] $ids Operations; empty for every one of the nature.
     * @param string $choice overwrite or skip.
     * @param string $nature The decision to settle when no ids are given.
     * @return int How many were settled.
     */
    private static function files(int $planid, array &$document, array $ids, string $choice, string $nature): int {
        global $DB;
        if (!in_array($choice, self::FILE_CHOICES, true)) {
            throw new \invalid_parameter_exception('Invalid file conflict resolution');
        }
        $conditions = ['planid' => $planid];
        if ($ids) {
            $records = [];
            foreach (array_chunk(array_map('intval', $ids), 500) as $chunk) {
                [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'id');
                $records += $DB->get_records_select(
                    'local_filebank_operation',
                    'planid = :planid AND id ' . $insql,
                    $params + ['planid' => $planid],
                    'id',
                    'id, sourcehash, targetcourseid, decision'
                );
            }
        } else {
            $nature = $nature !== '' ? $nature : 'blocked_content';
            $records = $DB->get_records(
                'local_filebank_operation',
                $conditions + ['decision' => $nature],
                'id',
                'id, sourcehash, targetcourseid, decision'
            );
        }
        $changed = 0;
        $excluded = array_fill_keys($document['excluded'], true);
        foreach ($records as $record) {
            if (!str_starts_with($record->decision, 'blocked_')) {
                continue;
            }
            if ($choice === 'overwrite' && $record->decision !== 'blocked_content') {
                throw new \invalid_parameter_exception('Only a different file can be overwritten');
            }
            $key = $record->sourcehash . ':' . $record->targetcourseid;
            $document['filechoices'][$key] = $choice;
            unset($excluded[$key]);
            $changed++;
        }
        $document['excluded'] = array_keys($excluded);
        return $changed;
    }

    /**
     * Settle issues that were found while the migration was worked out.
     *
     * @param array $document Document of the migration.
     * @param int[] $ids Positions in the list of issues; empty for every one of the nature.
     * @param string $choice exclude, folder:ID or create:ID.
     * @param string $nature The reason to settle when no ids are given.
     * @return int How many were settled.
     */
    private static function issues(array &$document, array $ids, string $choice, string $nature): int {
        $changed = 0;
        $ids = $ids ?: array_keys(array_filter(
            $document['issues'] ?? [],
            static fn(array $issue): bool => $nature === '' || ($issue['reason'] ?? '') === $nature
        ));
        foreach ($ids as $id) {
            $issue = $document['issues'][(int) $id] ?? null;
            if (!$issue) {
                throw new \invalid_parameter_exception('Invalid migration issue');
            }
            if ($choice === 'exclude' || $choice === 'ignore') {
                if (!empty($issue['sourcecmid']) && !empty($issue['targetcourseid'])) {
                    $document['placements'][$issue['sourcecmid'] . ':' . $issue['targetcourseid']] = ['exclude' => true];
                } else {
                    $document['ignoredissues'][] = hash('sha256', json_encode($issue));
                }
            } else {
                $document['placements'][$issue['sourcecmid'] . ':' . $issue['targetcourseid']] = self::destination($issue, $choice);
            }
            $changed++;
        }
        return $changed;
    }

    /**
     * The destination a person chose for a folder that has more than one possible place.
     *
     * @param array $issue The issue.
     * @param string $choice folder:ID or create:ID.
     * @return array The placement.
     */
    private static function destination(array $issue, string $choice): array {
        global $DB;
        if (
            !in_array($issue['reason'], ['placementambiguous', 'creationnamerequired'], true)
            || empty($issue['sourcecmid']) || empty($issue['targetcourseid'])
            || !preg_match('/^(folder|create):([1-9][0-9]*)$/', $choice, $matches)
        ) {
            throw new \invalid_parameter_exception('Invalid destination resolution');
        }
        $courseid = (int) $issue['targetcourseid'];
        require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
        if ($matches[1] === 'folder') {
            $cm = get_coursemodule_from_id('folder', (int) $matches[2], $courseid, false, MUST_EXIST);
            return ['cmid' => (int) $cm->id, 'sectionid' => (int) $cm->section];
        }
        require_capability('mod/folder:addinstance', \context_course::instance($courseid));
        $section = $DB->get_record('course_sections', ['id' => (int) $matches[2], 'course' => $courseid], '*', MUST_EXIST);
        return ['create' => true, 'sectionid' => (int) $section->id];
    }

    /**
     * What is out of the migration, from the document and from what was unchecked in the review.
     *
     * @param int $planid The migration.
     * @param array $document Document of the migration.
     * @return string[] Keys of the files that stay out.
     */
    private static function excluded(int $planid, array $document): array {
        global $DB;
        $excluded = array_fill_keys($document['excluded'] ?? [], true);
        $records = $DB->get_recordset(
            'local_filebank_operation',
            ['planid' => $planid],
            'id',
            'id, sourcehash, targetcourseid, included, decision'
        );
        foreach ($records as $record) {
            $key = $record->sourcehash . ':' . $record->targetcourseid;
            if ($record->included) {
                unset($excluded[$key]);
            } else if (!str_starts_with($record->decision, 'blocked_') && $record->decision !== 'skip_conflict') {
                $excluded[$key] = true;
            }
        }
        $records->close();
        return array_keys($excluded);
    }
}
