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
 * Bounded operational history retention.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\maintenance;

/**
 * Removes expired operational history in bounded, indexed batches.
 */
final class retention {
    /**
     * Remove old logs and inactive plans; never remove files or original authorship.
     *
     * @param int|null $now Reference timestamp.
     * @return array Deleted record counts.
     */
    public static function clean(?int $now = null): array {
        global $DB;
        $now = $now ?? time();
        $counts = ['logs' => 0, 'plans' => 0];
        $logdays = get_config('local_multipicker', 'logretentiondays');
        $plandays = get_config('local_multipicker', 'migrationretentiondays');
        $logdays = $logdays === false ? 90 : (int)$logdays;
        $plandays = $plandays === false ? 365 : (int)$plandays;
        if ($logdays > 0) {
            $logs = $DB->get_records_select(
                'local_multipicker_log',
                'timecreated < ?',
                [$now - $logdays * DAYSECS],
                'timecreated, id',
                'id',
                0,
                1000
            );
            if ($logs) {
                $DB->delete_records_list('local_multipicker_log', 'id', array_keys($logs));
                $counts['logs'] = count($logs);
            }
        }
        if ($plandays > 0) {
            $cutoff = $now - $plandays * DAYSECS;
            $plans = $DB->get_records_sql('SELECT p.id FROM {local_multipicker_plan} p
                WHERE p.timecreated < ? AND NOT EXISTS (SELECT 1 FROM {local_multipicker_run} r
                    WHERE r.planid = p.id AND (r.state = ? OR r.state = ? OR r.timemodified >= ?))
                ORDER BY p.timecreated, p.id', [$cutoff, 'queued', 'running', $cutoff], 0, 100);
            foreach ($plans as $plan) {
                $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('plan:' . $plan->id, 0);
                if (!$lock) {
                    continue;
                }
                try {
                    $transaction = $DB->start_delegated_transaction();
                    if (
                        !$DB->record_exists_sql(
                            'SELECT id FROM {local_multipicker_run}
                        WHERE planid = ? AND (state = ? OR state = ? OR timemodified >= ?)',
                            [$plan->id, 'queued', 'running', $cutoff]
                        )
                    ) {
                        $DB->delete_records('local_multipicker_target', ['planid' => $plan->id]);
                        $DB->delete_records('local_multipicker_operation', ['planid' => $plan->id]);
                        $DB->delete_records('local_multipicker_run', ['planid' => $plan->id]);
                        $DB->delete_records('local_multipicker_plan', ['id' => $plan->id]);
                        $counts['plans']++;
                    }
                    $transaction->allow_commit();
                } finally {
                    $lock->release();
                }
            }
        }
        $counts['versions'] = \local_multipicker\local\profile\profile_history::clean($now);
        return $counts;
    }
}
