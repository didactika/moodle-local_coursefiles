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
 * A page of the files of a migration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration\view;

use local_filebank\output\display;

/**
 * Lists the files of a migration, or its folders, a page at a time and filtered by the server, because a migration can have more
 * files than a browser should be sent.
 */
final class migration_files {
    /**
     * The groups the files can be filtered by.
     */
    public const BUCKETS = ['', 'copy', 'existing', 'left'];

    /**
     * Read a page of files.
     *
     * @param int $planid The migration.
     * @param string $bucket One of the groups, or an empty string for all of them.
     * @param string $search Part of a file, folder or course name.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The counts of every group, the total of the filter and the files of the page.
     */
    public static function page(int $planid, string $bucket, string $search, int $page, int $perpage): array {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        if (!in_array($bucket, self::BUCKETS, true)) {
            throw new \invalid_parameter_exception('Unknown file group');
        }
        $perpage = max(1, min(100, $perpage));
        $page = max(0, $page);
        [$where, $params] = self::filter($planid, $bucket, $search);
        $total = $DB->count_records_sql('SELECT COUNT(1) FROM {local_filebank_operation} o WHERE ' . $where, $params);
        $records = $DB->get_records_sql(
            'SELECT o.* FROM {local_filebank_operation} o WHERE ' . $where . ' ORDER BY o.sourcecourseid, o.sourcecmid, o.id',
            $params,
            $page * $perpage,
            $perpage
        );
        $names = new course_names();
        foreach ($records as $record) {
            $names->load([$record->sourcecourseid, $record->targetcourseid]);
        }
        $rows = [];
        foreach ($records as $record) {
            $rows[] = self::row($record, $names);
        }
        return ['counts' => self::counts($planid), 'total' => $total, 'files' => $rows];
    }

    /**
     * Read a page of the folders the files come from and go to.
     *
     * @param int $planid The migration.
     * @param string $search Part of a course name.
     * @param int $page Zero-based page.
     * @param int $perpage Page size.
     * @return array The total of folders and the folders of the page.
     */
    public static function folders(int $planid, string $search, int $page, int $perpage): array {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        $perpage = max(1, min(100, $perpage));
        [$where, $params] = self::filter($planid, '', $search);
        $total = $DB->count_records_sql(
            'SELECT COUNT(1) FROM (SELECT 1 FROM {local_filebank_operation} o WHERE ' . $where
            . ' GROUP BY o.sourcecourseid, o.sourcecmid, o.targetcourseid) g',
            $params
        );
        $records = $DB->get_records_sql(
            'SELECT MIN(o.id) AS id, o.sourcecourseid, o.sourcecmid, o.targetcourseid, COUNT(1) AS total,
                    SUM(o.included) AS included
               FROM {local_filebank_operation} o
              WHERE ' . $where . '
           GROUP BY o.sourcecourseid, o.sourcecmid, o.targetcourseid
           ORDER BY o.sourcecourseid, o.sourcecmid, o.targetcourseid',
            $params,
            max(0, $page) * $perpage,
            $perpage
        );
        $names = new course_names();
        foreach ($records as $record) {
            $names->load([$record->sourcecourseid, $record->targetcourseid]);
        }
        $folders = [];
        foreach ($records as $record) {
            $first = $DB->get_record('local_filebank_operation', ['id' => $record->id], 'id, snapshot', MUST_EXIST);
            $snapshot = json_decode($first->snapshot, true);
            $folders[] = ['sourcecourseid' => (int) $record->sourcecourseid, 'sourcecmid' => (int) $record->sourcecmid,
                'sourcecourse' => $names->name((int) $record->sourcecourseid),
                'sourcefolder' => display::plain($snapshot['sourcefoldername'] ?? ''),
                'targetcourse' => $names->name((int) $record->targetcourseid),
                'targetfolder' => display::plain($snapshot['target']['name'] ?? ''),
                'total' => (int) $record->total, 'included' => (int) $record->included];
        }
        return ['total' => $total, 'folders' => $folders];
    }

    /**
     * The files that match a group and a search.
     *
     * @param int $planid The migration.
     * @param string $bucket One of the groups, or an empty string.
     * @param string $search Part of a name.
     * @return array The condition and its parameters.
     */
    private static function filter(int $planid, string $bucket, string $search): array {
        global $DB;
        $where = 'o.planid = :planid';
        $params = ['planid' => $planid];
        $groups = [
            'copy' => ["o.decision IN ('copy', 'overwrite')", []],
            'existing' => ["o.decision IN ('skip_identical', 'skip_content')", []],
            'left' => ["o.decision = 'skip_conflict'", []],
        ];
        if ($bucket !== '') {
            $where .= ' AND ' . $groups[$bucket][0];
            $params += $groups[$bucket][1];
        }
        if ($search !== '') {
            $where .= ' AND (' . $DB->sql_like('o.snapshot', ':snapshotsearch', false)
                . ' OR EXISTS (SELECT 1 FROM {course} c WHERE (c.id = o.sourcecourseid OR c.id = o.targetcourseid) AND '
                . $DB->sql_like('c.shortname', ':coursesearch', false) . '))';
            $params['snapshotsearch'] = '%' . $DB->sql_like_escape($search) . '%';
            $params['coursesearch'] = $params['snapshotsearch'];
        }
        return [$where, $params];
    }

    /**
     * How many files each group has.
     *
     * @param int $planid The migration.
     * @return array The counts of the groups, and of all the files.
     */
    private static function counts(int $planid): array {
        global $DB;
        $counts = ['all' => 0, 'copy' => 0, 'existing' => 0, 'left' => 0];
        $rows = $DB->get_records_sql(
            'SELECT decision, COUNT(1) AS total FROM {local_filebank_operation} WHERE planid = :planid GROUP BY decision',
            ['planid' => $planid]
        );
        foreach ($rows as $row) {
            $group = in_array($row->decision, ['copy', 'overwrite'], true) ? 'copy'
                : (in_array($row->decision, ['skip_identical', 'skip_content'], true) ? 'existing'
                : 'left');
            $counts[$group] += (int) $row->total;
            $counts['all'] += (int) $row->total;
        }
        return $counts;
    }

    /**
     * One file of the page.
     *
     * @param \stdClass $record The operation.
     * @param course_names $names Course names.
     * @return array The file.
     */
    private static function row(\stdClass $record, course_names $names): array {
        $snapshot = json_decode($record->snapshot, true);
        $result = json_decode($record->result, true) ?: [];
        $source = $snapshot['source'];
        $target = $snapshot['target'];
        return ['id' => (int) $record->id, 'filename' => $source['filename'], 'filepath' => $source['filepath'],
            'sourcecourse' => $names->name((int) $record->sourcecourseid),
            'sourcefolder' => display::plain($snapshot['sourcefoldername'] ?? ''),
            'targetcourse' => $names->name((int) $record->targetcourseid), 'targetfolder' => display::plain($target['name'] ?? ''),
            'decision' => $record->decision, 'decisionlabel' => display::outcome($record->decision),
            'included' => (bool) $record->included,
            'settable' => !str_starts_with($record->decision, 'blocked_') && $record->decision !== 'skip_conflict',
            'state' => $record->state, 'statelabel' => display::outcome($record->state),
            'result' => isset($result['error']) ? display::reason($result['error'])
                : (!empty($result['outcome']) ? display::outcome($result['outcome']) : ''),
            'author' => $source['author'] ?: get_string('unknownauthor', 'local_filebank'),
            'license' => (string) $source['license']];
    }
}
