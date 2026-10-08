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
 * Authorized asset grouping with keyset pagination.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local\catalog;

use local_coursebridge\local\profile\profiles;

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
     * @return array Grouped assets and next cursor.
     */
    public static function search(
        int $targetcourse,
        int $profileid,
        array $filters = [],
        string $after = '',
        int $limit = 20
    ): array {
        global $DB;
        require_capability('local/coursebridge:browse', \context_course::instance($targetcourse));
        require_capability('local/coursebridge:reuse', \context_course::instance($targetcourse));
        require_capability('moodle/course:manageactivities', \context_course::instance($targetcourse));
        if ($after !== '' && !preg_match('/^[a-f0-9]{40}$/', $after)) {
            throw new \moodle_exception('invalidcursor', 'local_coursebridge');
        }
        [$profile, $revision, $rules] = profiles::load($profileid);
        $identity = $rules->resolve(get_course($targetcourse));
        if (!$identity) {
            return ['assets' => [], 'next' => ''];
        }
        $limit = max(1, min(50, $limit));
        $params = ['revisionid' => $revision->id, 'familyhash' => $identity['familyhash'], 'after' => $after];
        $where = 'c.revisionid = :revisionid AND c.familyhash = :familyhash AND f.contenthash > :after';
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
        $sql = 'SELECT f.*, c.edition FROM {local_coursebridge_file} f
                  JOIN {local_coursebridge_course} c ON c.courseid = f.courseid
                 WHERE ' . $where . ' ORDER BY f.contenthash, f.id';
        $records = $DB->get_recordset_sql($sql, $params);
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
                        [$profile, $revision, $rules],
                        $scope
                    );
                } catch (\moodle_exception $exception) {
                    continue;
                }
                if (
                    $file->get_contenthash() !== $record->contenthash
                        || (!empty($filters['edition']) && $filters['edition'] !== $sourceidentity['edition'])
                        || (!empty($filters['module']) && $filters['module'] !== $cm->modname)
                        || (!empty($filters['shortname']) && $filters['shortname'] !== $course->shortname)
                        || (!empty($filters['mimetype']) && $filters['mimetype'] !== $file->get_mimetype())
                        || (isset($filters['section']) && $filters['section'] !== ''
                            && (int)$filters['section'] !== (int)$cm->section)
                        || (!empty($filters['q'])
                            && \core_text::strpos(
                                \core_text::strtolower($file->get_filename()),
                                \core_text::strtolower($filters['q'])
                            ) === false)
                ) {
                    continue;
                }
                $key = $file->get_contenthash() . ':' . $file->get_filesize();
                if (!isset($assets[$key])) {
                    $assets[$key] = ['contenthash' => $key, 'filesize' => $file->get_filesize(), 'occurrences' => []];
                }
                $assets[$key]['occurrences'][] = [
                    'pathnamehash' => $file->get_pathnamehash(), 'filename' => $file->get_filename(),
                    'filepath' => $file->get_filepath(), 'author' => $file->get_author(), 'license' => $file->get_license(),
                    'courseid' => $cm->course, 'shortname' => $course->shortname, 'edition' => $sourceidentity['edition'],
                    'cmid' => $cm->id, 'module' => $cm->modname, 'modulename' => $cm->name, 'sectionid' => $cm->section,
                ];
            }
        } finally {
            $records->close();
        }
        return ['assets' => array_values($assets), 'next' => $next];
    }
}
