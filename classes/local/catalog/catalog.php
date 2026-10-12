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
 * Groups current permitted occurrences rather than global unfiltered results.
 */
final class catalog {
    /**
     * Search a target family with an opaque content cursor.
     *
     * @param int $targetcourse Course.
     * @param int $profileid Profile.
     * @param array $filters Filename, edition, module, MIME, shortname and section.
     * @param string $after Last complete content hash.
     * @param int $limit Page size.
     * @param int $draftid Current actor's destination draft; zero leaves catalog browsing unchanged.
     * @return array Grouped assets and next cursor.
     */
    public static function search(
        int $targetcourse,
        int $profileid,
        array $filters = [],
        string $after = '',
        int $limit = 20,
        int $draftid = 0
    ): array {

        global $DB;
        require_capability('local/filebank:browse', \context_course::instance($targetcourse));
        require_capability('local/filebank:reuse', \context_course::instance($targetcourse));
        \local_filebank\local\selection\destination_access::require($targetcourse);
        if ($after !== '' && !preg_match('/^[a-f0-9]{40}$/', $after)) {
            throw new \moodle_exception('invalidcursor', 'local_filebank');
        }
        [$profile, $revision, $rules] = profiles::load($profileid);
        $identity = $rules->resolve(get_course($targetcourse));
        if (!$identity) {
            return ['assets' => [], 'next' => ''];
        }
        $limit = max(1, min(50, $limit));
        [$sql, $params, $capturefilters] = catalog_query::build(
            $revision->id,
            $identity['familyhash'],
            $filters,
            $rules->filter_definitions(),
            $after,
            $draftid
        );
        $records = $DB->get_recordset_sql($sql, $params);
        return self::collect($records, $targetcourse, $profileid, [$profile, $revision, $rules], $filters, $capturefilters, $limit);
    }

    /**
     * Collect deduplicated occurrences while retaining the content cursor and authorization cache.
     *
     * @param \moodle_recordset $records Bounded indexed candidates.
     * @param int $targetcourse Owning destination course.
     * @param int $profileid Active profile.
     * @param array $profiledata Validated active profile and revision.
     * @param array $filters Search values.
     * @param array $capturefilters Validated capture values.
     * @param int $limit Bounded result count.
     * @return array Result.
     */
    private static function collect(
        \moodle_recordset $records,
        int $targetcourse,
        int $profileid,
        array $profiledata,
        array $filters,
        array $capturefilters,
        int $limit
    ): array {
        $assets = [];
        $next = '';
        $currenthash = '';
        $scanned = 0;
        $scope = [];
        try {
            foreach ($records as $record) {
                if ($record->contenthash !== $currenthash) {
                    if (count($assets) >= $limit || $scanned >= 10000) {
                        $next = $currenthash;
                        break;
                    }
                    $currenthash = $record->contenthash;
                }
                $scanned++;
                try {
                    [$file, $cm, $sourceidentity, $course] = material_source::resolve(
                        $record->pathnamehash,
                        $targetcourse,
                        $profileid,
                        $profiledata,
                        $scope
                    );
                } catch (\moodle_exception $exception) {
                    continue;
                }
                if (!catalog_match::allows($file, $record, $cm, $sourceidentity, $course, $filters, $capturefilters)) {
                    continue;
                }
                $key = $file->get_contenthash() . ':' . $file->get_filesize();
                if (!isset($assets[$key])) {
                    $assets[$key] = ['contenthash' => $key, 'filesize' => $file->get_filesize(), 'occurrences' => []];
                }
                $assets[$key]['occurrences'][] = [
                    'pathnamehash' => $file->get_pathnamehash(), 'filename' => $file->get_filename(),
                    'filepath' => $file->get_filepath(), 'filearea' => $file->get_filearea(),
                    'author' => $file->get_author(), 'license' => $file->get_license(),
                    'courseid' => $cm->course, 'shortname' => $course->shortname, 'edition' => $sourceidentity['edition'],
                    'captures' => $sourceidentity['filters'],
                    'cmid' => $cm->id, 'module' => $cm->modname, 'modulename' => $cm->name, 'sectionid' => $cm->section,
                ];
            }
        } finally {
            $records->close();
        }
        return ['assets' => array_values($assets), 'next' => $next];
    }
}
