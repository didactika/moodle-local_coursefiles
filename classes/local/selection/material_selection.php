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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Bounded file and recursive folder selection.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

use local_coursebridge\local\catalog\material_source;

/**
 * Expands bounded native occurrences without relying on catalog cache freshness.
 */
final class material_selection {
    /** @var int Synchronous batch bound, including selected folder contents. */
    public const MAX_FILES = 200;

    /**
     * Resolve files and recursive folder paths, deduplicating overlapping selections.
     *
     * @param int $courseid Destination course.
     * @param int $profileid Enabled relationship profile.
     * @param array $items File locators or folder module/path selectors.
     * @return array Authorized stored files keyed by pathname hash.
     */
    public static function resolve(int $courseid, int $profileid, array $items): array {
        global $DB;
        if (!$items || count($items) > self::MAX_FILES) {
            throw new \moodle_exception('invalidselection', 'local_coursebridge');
        }
        $files = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new \moodle_exception('invalidselection', 'local_coursebridge');
            }
            if (isset($item['pathnamehash'])) {
                [$file] = material_source::resolve((string)$item['pathnamehash'], $courseid, $profileid);
                $files[$file->get_pathnamehash()] = $file;
            } else if (isset($item['cmid'], $item['filepath'])) {
                $path = self::path((string)$item['filepath']);
                $cm = get_coursemodule_from_id('folder', (int)$item['cmid'], 0, false, MUST_EXIST);
                $context = \context_module::instance($cm->id);
                $conditions = 'contextid = :contextid AND component = :component AND filearea = :filearea'
                    . ' AND itemid = 0 AND filename <> :directory AND '
                    . $DB->sql_like('filepath', ':path', true);
                $params = ['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
                    'directory' => '.', 'path' => $DB->sql_like_escape($path) . '%'];
                $candidates = $DB->get_records_select(
                    'files', $conditions, $params, 'filepath, filename, id', 'id, pathnamehash', 0, self::MAX_FILES + 1
                );
                if (!$candidates || count($candidates) > self::MAX_FILES) {
                    throw new \moodle_exception('invalidselection', 'local_coursebridge');
                }
                foreach ($candidates as $candidate) {
                    [$file] = material_source::resolve($candidate->pathnamehash, $courseid, $profileid);
                    $files[$file->get_pathnamehash()] = $file;
                }
            } else {
                throw new \moodle_exception('invalidselection', 'local_coursebridge');
            }
            if (count($files) > self::MAX_FILES) {
                throw new \moodle_exception('invalidselection', 'local_coursebridge');
            }
        }
        return $files;
    }

    /**
     * Require an exact relative directory path without traversal or wildcard normalization.
     *
     * @param string $path Native path.
     * @return string Validated path.
     */
    public static function path(string $path): string {
        if (
            $path === '' || $path[0] !== '/' || substr($path, -1) !== '/' || str_contains($path, '\\')
                || clean_param($path, PARAM_PATH) !== $path
                || preg_match('~(?:^|/)\.\.?(/|$)|//~', $path)
        ) {
            throw new \moodle_exception('invalidselection', 'local_coursebridge');
        }
        return $path;
    }
}
