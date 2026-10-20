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
 * Changes the parameters of a migration, inside the same migration: the operations are worked out again and stored where they were,
 * so no other migration is created.
 */
final class plan_editor {
    /** @var string The change has to be made on a migration that is of the current revision of the profile. */
    private const KEEP = 'keep';

    /** @var string The change works the migration out again with the current revision of the profile. */
    private const REBASE = 'rebase';

    /**
     * Change the parameters of a migration: the courses it goes between, the correspondences and the policies.
     *
     * @param int $planid The migration.
     * @param string $digest What the person is looking at.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param string $scopekey Filter dimension used for the edition-like labels.
     * @param array $options New filters and migration configuration; what is not given is kept.
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
            foreach (['migration', 'filters'] as $key) {
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
