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
 * Authorized asset grouping with keyset pagination.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\catalog;

use local_filebank\local\profile\profiles;

/**
 * Build parameterized family, public material and destination exclusion predicates.
 */
final class catalog_query {
    /**
     * Build bounded search SQL with named parameters and destination exclusion.
     *
     * @param int $revisionid Active profile revision.
     * @param string $familyhash Resolved course family.
     * @param array $filters Search values.
     * @param array $definitions Configured capture filter definitions.
     * @param string $after Content cursor.
     * @param int $draftid Actor-owned destination draft.
     * @return array Result.
     */
    public static function build(
        int $revisionid,
        string $familyhash,
        array $filters,
        array $definitions,
        string $after,
        int $draftid
    ): array {
        $params = ['revisionid' => $revisionid, 'familyhash' => $familyhash, 'after' => $after];
        $where = 'c.revisionid = :revisionid AND c.familyhash = :familyhash AND f.contenthash > :after';
        [$exclusion, $destinationparams] = \local_filebank\local\selection\destination_content::exclusion('n', $draftid);
        $where .= $exclusion;
        $params += $destinationparams;
        self::scalar_filters($filters, $where, $params);
        self::file_filters($filters, $where, $params);
        $capturefilters = self::capture_values($filters, $definitions);
        self::capture_filters($capturefilters, $where, $params);
        $sql = 'SELECT f.*, c.edition FROM {local_filebank_file} f
                  JOIN {local_filebank_course} c ON c.courseid = f.courseid
                  JOIN {files} n ON n.pathnamehash = f.pathnamehash
                  JOIN {course_modules} mc ON mc.id = f.cmid AND mc.course = f.courseid
                  JOIN {modules} mo ON mo.id = mc.module
                  JOIN {course} sc ON sc.id = f.courseid
                 WHERE ' . $where . ' ORDER BY f.contenthash, f.id';
        return [$sql, $params, $capturefilters];
    }
    /**
     * Scalar filters.
     *
     * @param array $filters Search values.
     * @param string $where SQL predicates.
     * @param array $params Named query parameters.
     */
    private static function scalar_filters(array $filters, string &$where, array &$params): void {
        global $DB;

        if (!empty($filters['excludedcontext'])) {
            $where .= ' AND n.contextid <> :excludedcontext';
            $params['excludedcontext'] = (int)$filters['excludedcontext'];
        }
        if (!empty($filters['cmid'])) {
            $where .= ' AND f.cmid = :cmid';
            $params['cmid'] = (int)$filters['cmid'];
        }
        if (!empty($filters['sourcepath'])) {
            $sourcepath = \local_filebank\local\selection\material_selection::path($filters['sourcepath']);
            $where .= ' AND ' . $DB->sql_like('n.filepath', ':sourcepath', true);
            $params['sourcepath'] = $DB->sql_like_escape($sourcepath) . '%';
        }
        if (!empty($filters['module'])) {
            $where .= ' AND mo.name = :module';
            $params['module'] = $filters['module'];
        }
        if (!empty($filters['shortname'])) {
            $where .= ' AND sc.shortname = :shortname';
            $params['shortname'] = $filters['shortname'];
        }
    }
    /**
     * File filters.
     *
     * @param array $filters Search values.
     * @param string $where SQL predicates.
     * @param array $params Named query parameters.
     */
    private static function file_filters(array $filters, string &$where, array &$params): void {
        global $DB;

        foreach (['edition' => 'c.edition', 'q' => 'f.filename', 'mimetype' => 'f.mimetype'] as $key => $field) {
            if (!empty($filters[$key])) {
                if ($key === 'q') {
                    $where .= ' AND ' . $DB->sql_like($field, ':' . $key, false);
                    $params[$key] = '%' . $DB->sql_like_escape($filters[$key]) . '%';
                } else {
                    $where .= ' AND ' . $field . ' = :' . $key;
                    $params[$key] = $filters[$key];
                }
            }
        }
    }
    /**
     * Capture values.
     *
     * @param array $filters Submitted search values.
     * @param array $definitions Configured filters.
     * @return array Result.
     */
    private static function capture_values(array $filters, array $definitions): array {
        $capturefilters = $filters['captures'] ?? [];
        if (!is_array($capturefilters) || array_diff(array_keys($capturefilters), array_keys($definitions))) {
            throw new \invalid_parameter_exception('Unknown capture filter');
        }
        return $capturefilters;
    }
    /**
     * Capture filters.
     *
     * @param array $capturefilters Validated capture values.
     * @param string $where SQL predicates.
     * @param array $params Named query parameters.
     */
    private static function capture_filters(array $capturefilters, string &$where, array &$params): void {
        foreach ($capturefilters as $name => $value) {
            if (!is_string($value) || strlen($value) > 255) {
                throw new \invalid_parameter_exception('Invalid capture filter value');
            }
            if ($value !== '') {
                $i = count($params);
                $where .= ' AND EXISTS (SELECT 1 FROM {local_filebank_value} v WHERE v.courseid = c.courseid'
                    . ' AND v.revisionid = c.revisionid AND v.name = :capturekey' . $i . ' AND v.value = :capturevalue' . $i . ')';
                $params['capturekey' . $i] = $name;
                $params['capturevalue' . $i] = $value;
            }
        }
    }
}
