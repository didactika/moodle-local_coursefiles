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
 * What a migration would do, in numbers.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration;

/**
 * The counters that the pages of a migration show: the files that would be copied or overwritten, the ones that are already in the
 * destination, the ones that wait for a decision, the folders and the courses involved, and what was left out because it did not
 * match.
 */
final class migration_summary {
    /**
     * Count what a set of operations and a document say, before anything is stored.
     *
     * @param array $document The document of the migration.
     * @param array[] $operations The operations that were worked out.
     * @return array The counters.
     */
    public static function summarise(array $document, array $operations): array {
        $summary = self::empty();
        $folders = [];
        $courses = [];
        $targets = [];
        foreach ($operations as $operation) {
            $folders[$operation['snapshot']['sourcecmid']] = true;
            $courses[$operation['snapshot']['sourcecourse']] = true;
            $targets[$operation['snapshot']['target']['courseid']] = true;
            self::count($summary, $operation['decision'], (bool) $operation['included']);
        }
        return self::finish($summary, $document, count($folders), count($courses), count($targets));
    }

    /**
     * Count what a stored migration says.
     *
     * @param int $planid The migration.
     * @return array The counters.
     */
    public static function stored(int $planid): array {
        global $DB;
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], 'id, configjson', MUST_EXIST);
        $summary = self::empty();
        $rows = $DB->get_records_sql(
            'SELECT decision, included, COUNT(1) AS total
               FROM {local_filebank_operation}
              WHERE planid = :planid
           GROUP BY decision, included',
            ['planid' => $planid]
        );
        foreach ($rows as $row) {
            self::count($summary, $row->decision, (bool) $row->included, (int) $row->total);
        }
        $folders = $DB->count_records_sql(
            'SELECT COUNT(DISTINCT sourcecmid) FROM {local_filebank_operation} WHERE planid = :planid',
            ['planid' => $planid]
        );
        $courses = $DB->count_records_sql(
            'SELECT COUNT(DISTINCT sourcecourseid) FROM {local_filebank_operation} WHERE planid = :planid',
            ['planid' => $planid]
        );
        $targets = $DB->count_records_sql(
            'SELECT COUNT(DISTINCT targetcourseid) FROM {local_filebank_operation} WHERE planid = :planid',
            ['planid' => $planid]
        );
        return self::finish($summary, json_decode($plan->configjson, true), $folders, $courses, $targets);
    }

    /**
     * The counters of a migration that has nothing in it.
     *
     * @return array The counters, all at zero.
     */
    private static function empty(): array {
        return ['files' => 0, 'tocopy' => 0, 'tooverwrite' => 0, 'existing' => 0, 'conflicts' => 0, 'skipped' => 0,
            'included' => 0];
    }

    /**
     * Add one kind of operation to the counters.
     *
     * @param array $summary The counters.
     * @param string $decision What was decided for the operations.
     * @param bool $included Whether they are part of the migration.
     * @param int $number How many operations there are.
     */
    private static function count(array &$summary, string $decision, bool $included, int $number = 1): void {
        $summary['files'] += $number;
        if ($included) {
            $summary['included'] += $number;
        }
        if (in_array($decision, ['skip_identical', 'skip_content'], true)) {
            $summary['existing'] += $number;
        } else if (str_starts_with($decision, 'blocked_')) {
            $summary['conflicts'] += $number;
        } else if ($included && $decision === 'overwrite') {
            $summary['tooverwrite'] += $number;
        } else if ($included && $decision === 'copy') {
            $summary['tocopy'] += $number;
        } else {
            $summary['skipped'] += $number;
        }
    }

    /**
     * Add what does not come from the operations.
     *
     * @param array $summary The counters.
     * @param array $document The document of the migration.
     * @param int $folders How many folders of the source are involved.
     * @param int $courses How many courses of the source are involved.
     * @param int $targets How many courses of the destination are involved.
     * @return array The counters.
     */
    private static function finish(array $summary, array $document, int $folders, int $courses, int $targets): array {
        $open = array_filter($document['issues'] ?? [], static fn(array $issue): bool => !empty($issue['reason']));
        return $summary + [
            'folders' => $folders,
            'sourcecourses' => $courses,
            'targetcourses' => $targets,
            'openissues' => count($open),
            'notes' => $document['notes'] ?? [],
            'haswork' => $summary['tocopy'] + $summary['tooverwrite'] > 0,
        ];
    }
}
