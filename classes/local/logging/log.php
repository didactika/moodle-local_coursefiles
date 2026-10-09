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
 * Structured actor and outcome log.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local\logging;

/**
 * Records actions without credentials or binaries.
 */
final class log {
    /**
     * Known actions available in the filter.
     *
     * @return array Action names.
     */
    public static function actions(): array {
        return ['profile_activated', 'site_policy_changed', 'course_policy_changed', 'publication_granted', 'publication_revoked',
            'migration_previewed', 'migration_queued', 'migration_blocked', 'migration_batch'];
    }

    /**
     * Read a bounded page using the last visible record as its cursor.
     *
     * @param string $action Exact action, or all actions.
     * @param string $entityuuid Exact reference, or all references.
     * @param int $before Read records preceding this ID.
     * @param int $limit Page size, bounded to 100.
     * @return array Records and the next cursor.
     */
    public static function page(string $action = '', string $entityuuid = '', int $before = 0, int $limit = 50): array {
        global $DB;
        require_capability('local/coursebridge:viewlog', \context_system::instance());
        $limit = max(1, min(100, $limit));
        $where = ['1 = 1'];
        $params = [];
        foreach (['action' => $action, 'entityuuid' => $entityuuid] as $field => $value) {
            if ($value !== '') {
                $where[] = $field . ' = :' . $field;
                $params[$field] = $value;
            }
        }
        if ($before > 0) {
            $where[] = 'id < :before';
            $params['before'] = $before;
        }
        $records = array_values($DB->get_records_select(
            'local_coursebridge_log',
            implode(' AND ', $where),
            $params,
            'id DESC',
            '*',
            0,
            $limit + 1
        ));
        $hasnext = count($records) > $limit;
        $records = array_slice($records, 0, $limit);
        return ['records' => $records, 'nextbefore' => $hasnext ? (int)end($records)->id : 0];
    }

    /**
     * Append an action.
     *
     * @param string $action Action.
     * @param string $uuid Entity.
     * @param array $details Outcome.
     */
    public static function record(string $action, string $uuid, array $details): void {
        global $DB, $USER;
        $DB->insert_record('local_coursebridge_log', (object)[
            'userid' => (int)($USER->id ?? 0), 'action' => $action, 'entityuuid' => $uuid,
            'details' => json_encode($details), 'timecreated' => time(),
        ]);
    }
}
