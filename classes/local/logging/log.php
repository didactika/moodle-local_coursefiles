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
 * Structured actor and outcome log.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\logging;

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
        return ['profile_state_changed', 'profile_activated', 'site_policy_changed', 'course_policy_changed',
            'publication_granted', 'publication_revoked',
            'migration_previewed', 'migration_selection_changed', 'migration_queued', 'migration_blocked', 'migration_batch',
            'migration_deleted', 'migration_empty_cancelled', 'migration_conflict_resolved', 'migration_updated',
            'migration_reconfigured', 'migration_empty_skipped'];
    }

    /**
     * Read a bounded page using the last visible record as its cursor.
     *
     * @param string $action Exact action, or all actions.
     * @param string $entityuuid Exact reference, or all references.
     * @param int $before Read records preceding this ID.
     * @param int $limit Page size, bounded to 100.
     * @param int $from Inclusive timestamp lower bound.
     * @param int $until Exclusive timestamp upper bound.
     * @param int $page Zero-based page within the filtered records.
     * @return array Records and the next cursor.
     */
    public static function page(
        string $action = '',
        string $entityuuid = '',
        int $before = 0,
        int $limit = 50,
        int $from = 0,
        int $until = 0,
        int $page = 0
    ): array {
        global $DB;
        require_capability('local/filebank:viewlog', \context_system::instance());
        $limit = max(1, min(\local_filebank\output\pagination::MAX_ALL, $limit));
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
        if ($from > 0) {
            $where[] = 'timecreated >= :fromdate';
            $params['fromdate'] = $from;
        }
        if ($until > 0) {
            $where[] = 'timecreated < :untildate';
            $params['untildate'] = $until;
        }
        $total = $DB->count_records_select('local_filebank_log', implode(' AND ', $where), $params);
        $page = \local_filebank\output\pagination::clamp($total, $page, $limit);
        $records = array_values($DB->get_records_select(
            'local_filebank_log',
            implode(' AND ', $where),
            $params,
            'id DESC',
            '*',
            $page * $limit,
            $limit + 1
        ));
        $hasnext = count($records) > $limit;
        $records = array_slice($records, 0, $limit);
        return ['records' => $records, 'nextbefore' => $hasnext ? (int)end($records)->id : 0,
            'total' => $total, 'page' => $page];
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
        $DB->insert_record('local_filebank_log', (object)[
            'userid' => (int)($USER->id ?? 0), 'action' => $action, 'entityuuid' => $uuid,
            'details' => json_encode($details), 'timecreated' => time(),
        ]);
    }
}
