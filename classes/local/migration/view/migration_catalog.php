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
 * The migrations of the site and the choices to start a new one.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration\view;

use local_filebank\local\migration\course_scope;
use local_filebank\local\migration\migration_rules;
use local_filebank\local\profile\profiles;
use local_filebank\output\display;

/**
 * Lists the migrations a page at a time, with their numbers, and describes what a new migration can be asked to do.
 */
final class migration_catalog {
    /**
     * The states a migration can be filtered by.
     */
    public const STATES = ['reviewable', 'queued', 'running', 'partial', 'completed', 'cancelled'];

    /**
     * Read a page of migrations, the newest first.
     *
     * @param string $search Part of the name of the profile.
     * @param string $state One of the states, or an empty string.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The total, the number of each state and the migrations of the page.
     */
    public static function page(string $search, string $state, int $page, int $perpage): array {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        if ($state !== '' && !in_array($state, self::STATES, true)) {
            throw new \invalid_parameter_exception('Unknown migration status');
        }
        $perpage = max(1, min(100, $perpage));
        $conditions = ['1 = 1'];
        $params = [];
        if ($search !== '') {
            $conditions[] = $DB->sql_like('f.name', ':search', false);
            $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
        }
        $from = ' FROM {local_filebank_plan} p JOIN {local_filebank} f ON f.id = p.profileid
            LEFT JOIN {local_filebank_run} r ON r.id = (SELECT MAX(last.id) FROM {local_filebank_run} last
                WHERE last.planid = p.id)';
        $counts = self::counts($from, $conditions, $params);
        if ($state !== '') {
            $conditions[] = 'COALESCE(r.state, p.state) = :state';
            $params['state'] = $state;
        }
        $where = ' WHERE ' . implode(' AND ', $conditions);
        $total = $DB->count_records_sql('SELECT COUNT(1)' . $from . $where, $params);
        $records = $DB->get_records_sql(
            'SELECT p.id, p.uuid, p.profileid, p.configjson, p.timecreated, f.name, COALESCE(r.state, p.state) AS status'
            . $from . $where . ' ORDER BY p.id DESC',
            $params,
            max(0, $page) * $perpage,
            $perpage
        );
        $totals = self::totals(array_keys($records));
        $migrations = [];
        foreach ($records as $record) {
            $document = json_decode($record->configjson, true);
            $own = $totals[$record->id] ?? ['files' => 0, 'towrite' => 0, 'conflicts' => 0, 'done' => 0];
            $migrations[] = ['id' => (int) $record->id, 'profile' => display::plain($record->name),
                'source' => self::side($document, 'source'), 'target' => self::side($document, 'target'),
                'state' => $record->status, 'statelabel' => display::outcome($record->status),
                'timecreated' => (int) $record->timecreated, 'datelabel' => userdate($record->timecreated),
                'files' => $own['files'], 'towrite' => $own['towrite'], 'conflicts' => $own['conflicts'], 'done' => $own['done'],
                'candelete' => !in_array($record->status, ['queued', 'running'], true)];
        }
        return ['total' => $total, 'counts' => $counts, 'migrations' => $migrations];
    }

    /**
     * What a new migration can be asked to do: the profiles, the filters of each profile and the rules to start from.
     *
     * @return array The profiles that are enabled, with their filters and rules, and the kinds of source a rule can read.
     */
    public static function options(): array {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        $profiles = [];
        foreach ($DB->get_records('local_filebank', ['enabled' => 1], 'name') as $profile) {
            [, , $rules] = profiles::load($profile->id);
            $dimensions = [];
            foreach (course_scope::choices($rules) as $key => $label) {
                $dimensions[] = ['key' => (string) $key, 'label' => strip_tags($label)];
            }
            $profiles[] = ['id' => (int) $profile->id, 'name' => display::plain($profile->name), 'dimensions' => $dimensions,
                'rules' => json_encode(migration_rules::to_ui($rules->get_config()['migration'] ?? []))];
        }
        $modules = [];
        foreach (\local_filebank\local\catalog\source_registry::module_names() as $module => $label) {
            $modules[] = ['value' => (string) $module, 'label' => (string) $label];
        }
        return ['profiles' => $profiles, 'modules' => $modules];
    }

    /**
     * How many migrations there are in each state, for the search that is applied.
     *
     * @param string $from The tables.
     * @param string[] $conditions The conditions of the search.
     * @param array $params Their parameters.
     * @return array The migrations of each state and in all.
     */
    private static function counts(string $from, array $conditions, array $params): array {
        global $DB;
        $counts = array_fill_keys(self::STATES, 0) + ['all' => 0];
        $rows = $DB->get_records_sql(
            'SELECT COALESCE(r.state, p.state) AS status, COUNT(1) AS total' . $from
            . ' WHERE ' . implode(' AND ', $conditions) . ' GROUP BY COALESCE(r.state, p.state)',
            $params
        );
        foreach ($rows as $row) {
            if (isset($counts[$row->status])) {
                $counts[$row->status] += (int) $row->total;
            }
            $counts['all'] += (int) $row->total;
        }
        return $counts;
    }

    /**
     * What the files of each migration of a page are doing.
     *
     * @param int[] $planids The migrations of the page.
     * @return array The files, the ones to write, the ones with a decision open and the ones done, by migration.
     */
    private static function totals(array $planids): array {
        global $DB;
        if (!$planids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($planids, SQL_PARAMS_NAMED, 'plan');
        $rows = $DB->get_records_sql(
            "SELECT planid AS id, COUNT(1) AS files,
                    SUM(CASE WHEN included = 1 AND decision IN ('copy', 'overwrite') THEN 1 ELSE 0 END) AS towrite,
                    SUM(CASE WHEN decision LIKE 'blocked%' THEN 1 ELSE 0 END) AS conflicts,
                    SUM(CASE WHEN included = 1 AND state <> 'pending' THEN 1 ELSE 0 END) AS done
               FROM {local_filebank_operation}
              WHERE planid $insql
           GROUP BY planid",
            $params
        );
        $totals = [];
        foreach ($rows as $row) {
            $totals[(int) $row->id] = ['files' => (int) $row->files, 'towrite' => (int) $row->towrite,
                'conflicts' => (int) $row->conflicts, 'done' => (int) $row->done];
        }
        return $totals;
    }

    /**
     * A side of a migration written as a short sentence.
     *
     * @param array $document Document of the migration.
     * @param string $side source or target.
     * @return string The values of its filters, or its edition.
     */
    public static function side(array $document, string $side): string {
        $values = array_filter($document['filters'][$side] ?? []);
        $label = $values ? implode(' / ', $values) : (string) ($document[$side . 'edition'] ?? '');
        return $label !== '' ? $label : get_string('allcourses', 'local_filebank');
    }
}
