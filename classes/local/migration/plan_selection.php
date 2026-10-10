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
 * Persistent reviewed migration inclusions independent of result pagination.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

/**
 * Serializes selection changes with queueing and invalidates older approvals.
 */
final class plan_selection {
    /**
     * Save at most one rendered page or an explicit whole-plan action.
     *
     * @param int $planid Stored plan.
     * @param string $digest Rendered approval.
     * @param array $visible IDs represented on this page.
     * @param array $included Checked IDs from that page.
     * @param string $action page, all, none or group.
     * @param array $group Explicit source course/folder and inclusion.
     */
    public static function save(
        int $planid,
        string $digest,
        array $visible,
        array $included,
        string $action = 'page',
        array $group = []
    ): void {
        global $DB;
        require_capability('local/multipicker:planmigration', \context_system::instance());
        $visible = array_values(array_unique(array_map('intval', $visible)));
        $included = array_values(array_unique(array_map('intval', $included)));
        if (!in_array($action, ['page', 'all', 'none', 'group'], true) || count($visible) > 50 || array_diff($included, $visible)) {
            throw new \invalid_parameter_exception('Invalid migration selection');
        }
        $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_multipicker');
        }
        try {
            $plan = $DB->get_record('local_multipicker_plan', ['id' => $planid], '*', MUST_EXIST);
            if (
                !hash_equals($plan->digest, $digest) || !hash_equals($digest, hash('sha256', $plan->configjson))
                    || $DB->record_exists('local_multipicker_run', ['planid' => $planid])
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            $transaction = $DB->start_delegated_transaction();
            if ($action === 'group') {
                foreach ($visible as $id) {
                    $DB->get_record('local_multipicker_operation', ['id' => $id, 'planid' => $planid], '*', MUST_EXIST);
                    $DB->set_field(
                        'local_multipicker_operation',
                        'included',
                        in_array($id, $included, true) ? 1 : 0,
                        ['id' => $id]
                    );
                }
                $conditions = ['planid' => $planid, 'sourcecourseid' => (int)($group['courseid'] ?? 0)];
                if (!empty($group['cmid'])) {
                    $conditions['sourcecmid'] = (int)$group['cmid'];
                }
                if (
                    !$conditions['sourcecourseid'] || !in_array($group['included'] ?? null, [0, 1], true)
                    || !$DB->record_exists('local_multipicker_operation', $conditions)
                ) {
                    throw new \invalid_parameter_exception('Invalid migration group');
                }
                $DB->set_field('local_multipicker_operation', 'included', $group['included'], $conditions);
            } else if ($action !== 'page') {
                $DB->set_field('local_multipicker_operation', 'included', $action === 'all' ? 1 : 0, ['planid' => $planid]);
            } else {
                foreach ($visible as $id) {
                    $operation = $DB->get_record(
                        'local_multipicker_operation',
                        ['id' => $id, 'planid' => $planid],
                        '*',
                        MUST_EXIST
                    );
                    $DB->set_field(
                        'local_multipicker_operation',
                        'included',
                        in_array($id, $included, true) ? 1 : 0,
                        ['id' => $operation->id]
                    );
                }
            }
            $document = json_decode($plan->configjson, true);
            $document['selectiondigest'] = self::digest($planid);
            $plan->configjson = json_encode($document, JSON_UNESCAPED_SLASHES);
            $plan->digest = hash('sha256', $plan->configjson);
            $blocked = $DB->record_exists_select(
                'local_multipicker_operation',
                'planid = ? AND included = 1 AND ' . $DB->sql_like('decision', '?'),
                [$planid, 'blocked%']
            );
            $issues = array_filter($document['issues'] ?? [], static fn($issue) => $issue['blocking'] ?? true);
            $plan->state = $issues || $blocked ? 'blocked' : (plan_history::has_work($planid) ? 'reviewable' : 'cancelled');
            $DB->update_record('local_multipicker_plan', $plan);
            \local_multipicker\local\logging\log::record(
                'migration_selection_changed',
                $plan->uuid,
                ['included' => $DB->count_records('local_multipicker_operation', ['planid' => $planid, 'included' => 1])]
            );
            $transaction->allow_commit();
        } finally {
            $lock->release();
        }
    }

    /**
     * Hash inclusions without loading the complete operation list into memory.
     *
     * @param int $planid Plan.
     * @return string Current selection digest.
     */
    public static function digest(int $planid): string {
        global $DB;
        $hash = hash_init('sha256');
        $records = $DB->get_recordset('local_multipicker_operation', ['planid' => $planid], 'id', 'id, included');
        foreach ($records as $record) {
            hash_update($hash, $record->id . ':' . $record->included . ';');
        }
        $records->close();
        return hash_final($hash);
    }

    /**
     * Fingerprint stored source/destination decisions without duplicating every snapshot in the plan document.
     *
     * @param int $planid Plan.
     * @return string Immutable operation digest.
     */
    public static function manifest_digest(int $planid): string {
        global $DB;
        $hash = hash_init('sha256');
        $records = $DB->get_recordset(
            'local_multipicker_operation',
            ['planid' => $planid],
            'id',
            'id, opkey, sourcehash, targetcmid, decision, snapshot'
        );
        foreach ($records as $record) {
            hash_update($hash, json_encode([$record->opkey, $record->sourcehash, $record->targetcmid,
                $record->decision, $record->snapshot]) . "\n");
        }
        $records->close();
        return hash_final($hash);
    }
}
