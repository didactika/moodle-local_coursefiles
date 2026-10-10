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
 * Explicit migration history removal and empty-plan classification.
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

/**
 * Removes inactive plan records without touching native material or attribution.
 */
final class plan_history {
    /**
     * Delete an inactive plan under the same lock used to queue execution.
     * @param int $planid Stored plan.
     */
    public static function delete(int $planid): void {
        global $DB;
        require_capability('local/multipicker:planmigration', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_multipicker');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            $plan = $DB->get_record('local_multipicker_plan', ['id' => $planid], '*', MUST_EXIST);
            if (
                $DB->record_exists_select(
                    'local_multipicker_run',
                    'planid = ? AND (state = ? OR state = ?)',
                    [$planid, 'queued', 'running']
                )
            ) {
                throw new \moodle_exception('migrationinuse', 'local_multipicker');
            }
            foreach (
                [
                'local_multipicker_target', 'local_multipicker_operation', 'local_multipicker_run', 'local_multipicker_plan',
                ] as $table
            ) {
                $DB->delete_records($table, [$table === 'local_multipicker_plan' ? 'id' : 'planid' => $planid]);
            }
            \local_multipicker\local\logging\log::record('migration_deleted', $plan->uuid, []);
            $transaction->allow_commit();
        } finally {
            $lock->release();
        }
    }

    /**
     * Whether an included operation can actually write material.
     * @param int $planid Plan.
     * @return bool At least one pending copy or overwrite.
     */
    public static function has_work(int $planid): bool {
        global $DB;
        return $DB->record_exists_select(
            'local_multipicker_operation',
            'planid = ? AND included = 1 AND state = ? AND (decision = ? OR decision = ?)',
            [$planid, 'pending', 'copy', 'overwrite']
        );
    }
}
