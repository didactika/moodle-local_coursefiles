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
 * Revalidated idempotent migration execution.
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
 * Executes immutable plans without silently repairing stale destinations.
 */
final class migration_executor {
    /**
     * Queue an accountable run after reviewing its digest.
     *
     * @param int $planid Plan.
     * @param string $digest Reviewed digest.
     * @return int Run.
     */
    public static function queue(int $planid, string $digest): int {

        global $DB, $USER;
        require_capability('local/filebank:executemigration', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        $profilelock = null;
        try {
            $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
            $profilelock = \core\lock\lock_config::get_lock_factory('local_filebank')
                ->get_lock('profile:' . $plan->profileid, 10);
            if (!$profilelock) {
                throw new \moodle_exception('lockfailed', 'local_filebank');
            }
            if (!migration_queue::validate($plan, $digest)) {
                return 0;
            }
            $transaction = $DB->start_delegated_transaction();
            $runid = $DB->insert_record(
                'local_filebank_run',
                (object)[
                    'planid' => $planid, 'userid' => $USER->id, 'state' => 'queued', 'cancelled' => 0,
                    'timecreated' => time(), 'timemodified' => time(),
                ]
            );
            $task = new \local_filebank\task\execute_migration();
            $task->set_userid($USER->id);
            $task->set_custom_data(['runid' => $runid]);
            \core\task\manager::queue_adhoc_task($task);
            log::record('migration_queued', $plan->uuid, ['runid' => $runid]);
            $transaction->allow_commit();
            return $runid;
        } finally {
            if ($profilelock) {
                $profilelock->release();
            }
            $lock->release();
        }
    }

    /**
     * Execute a bounded slice.
     *
     * @param int $runid Run.
     * @return bool More eligible operations remain.
     */
    public static function execute(int $runid): bool {

        global $DB, $USER;
        $run = $DB->get_record('local_filebank_run', ['id' => $runid], '*', MUST_EXIST);
        $plan = $DB->get_record('local_filebank_plan', ['id' => $run->planid], '*', MUST_EXIST);
        if ((int)$USER->id !== (int)$run->userid) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        require_capability('local/filebank:executemigration', \context_system::instance());
        if ($run->cancelled) {
            $DB->set_field('local_filebank_run', 'state', 'cancelled', ['id' => $runid]);
            return false;
        }
        if (!hash_equals($plan->digest, hash('sha256', $plan->configjson))) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        $DB->set_field('local_filebank_run', 'state', 'running', ['id' => $runid]);
        [, $revision, $rules] = profiles::load($plan->profileid);
        if ((int)$revision->id !== (int)$plan->revisionid) {
            $DB->set_field('local_filebank_run', 'state', 'blocked', ['id' => $runid]);
            log::record('migration_blocked', $plan->uuid, ['runid' => $runid, 'reason' => 'planstale']);
            return false;
        }
        $operations = $DB->get_records(
            'local_filebank_operation',
            ['planid' => $plan->id, 'state' => 'pending', 'included' => 1],
            'id',
            '*',
            0,
            25
        );
        foreach ($operations as $operation) {
            if ($DB->get_field('local_filebank_run', 'cancelled', ['id' => $runid])) {
                break;
            }
            migration_operation::execute($runid, $operation, $plan, $rules);
        }
        return self::finish($runid, $plan);
    }

    /**
     * Request cancellation at the next operation boundary.
     *
     * @param int $runid Run.
     */
    public static function cancel(int $runid): void {
        global $DB;
        require_capability('local/filebank:executemigration', \context_system::instance());
        $DB->set_field('local_filebank_run', 'cancelled', 1, ['id' => $runid]);
    }

    /**
     * Finish.
     *
     * @param int $runid Runid.
     * @param \stdClass $plan Plan.
     * @return bool Result.
     */
    private static function finish(int $runid, \stdClass $plan): bool {
        global $DB;
        $more = $DB->record_exists('local_filebank_operation', ['planid' => $plan->id, 'state' => 'pending', 'included' => 1]);
        $cancelled = $DB->get_field('local_filebank_run', 'cancelled', ['id' => $runid]);
        $state = $cancelled ? 'cancelled' : ($more ? 'running' : (
            $DB->record_exists('local_filebank_operation', ['planid' => $plan->id, 'state' => 'blocked', 'included' => 1])
                ? 'partial' : 'completed'));
        $DB->update_record(
            'local_filebank_run',
            (object)[
                'id' => $runid, 'state' => $state, 'timemodified' => time(),
            ]
        );
        log::record('migration_batch', $plan->uuid, ['runid' => $runid, 'state' => $state]);
        return $more && !$cancelled;
    }
}
