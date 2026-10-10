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
 * Explicit migration history removal and empty-plan classification.
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

/**
 * Removes inactive plan records without touching native material or attribution.
 */
final class plan_history {
    /**
     * Remove a reviewed, bounded selection atomically with respect to all selected queues.
     * @param array $ids Explicit plan IDs.
     */
    public static function delete_many(array $ids): void {
        global $DB;
        require_capability('local/multipicker:planmigration', \context_system::instance());
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);
        if (!$ids || count($ids) > 50 || min($ids) < 1) {
            throw new \invalid_parameter_exception('Invalid migration selection');
        }
        $locks = [];
        try {
            foreach ($ids as $id) {
                $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('plan:' . $id, 10);
                if (!$lock) {
                    throw new \moodle_exception('lockfailed', 'local_multipicker');
                }
                $locks[] = $lock;
                if (
                    $DB->record_exists_select(
                        'local_multipicker_run',
                        'planid = ? AND (state = ? OR state = ?)',
                        [$id, 'queued', 'running']
                    )
                ) {
                    throw new \moodle_exception('migrationinuse', 'local_multipicker');
                }
                $DB->get_record('local_multipicker_plan', ['id' => $id], '*', MUST_EXIST);
            }
            $transaction = $DB->start_delegated_transaction();
            foreach ($ids as $id) {
                $plan = $DB->get_record('local_multipicker_plan', ['id' => $id], '*', MUST_EXIST);
                foreach (
                    ['local_multipicker_target', 'local_multipicker_operation', 'local_multipicker_run',
                    'local_multipicker_plan'] as $table
                ) {
                    $DB->delete_records($table, [$table === 'local_multipicker_plan' ? 'id' : 'planid' => $id]);
                }
                \local_multipicker\local\logging\log::record('migration_deleted', $plan->uuid, []);
            }
            $transaction->allow_commit();
        } finally {
            foreach (array_reverse($locks) as $lock) {
                $lock->release();
            }
        }
    }

    /**
     * Delete an inactive plan under the same lock used to queue execution.
     * @param int $planid Stored plan.
     */
    public static function delete(int $planid): void {
        self::delete_many([$planid]);
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
