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
 * Recheck indexed search predicates against live authorized teaching files.
 */
final class catalog_match {
    /**
     * Recheck search predicates after resolving the current source permissions.
     *
     * @param \stored_file $file Current authorized file.
     * @param \stdClass $record Indexed occurrence.
     * @param \stdClass $cm Current course module.
     * @param array $identity Current source identity.
     * @param \stdClass $course Current source course.
     * @param array $filters Search values.
     * @param array $capturefilters Validated capture filters.
     * @return bool Result.
     */
    public static function allows(
        \stored_file $file,
        \stdClass $record,
        \stdClass $cm,
        array $identity,
        \stdClass $course,
        array $filters,
        array $capturefilters
    ): bool {
        if ($file->get_contenthash() !== $record->contenthash) {
            return false;
        }
        if (!self::fields($file, $cm, $identity, $course, $filters)) {
            return false;
        }
        if (!self::filename($file, $filters)) {
            return false;
        }
        foreach ($capturefilters as $name => $value) {
            if ($value !== '' && ($identity['filters'][$name] ?? '') !== $value) {
                return false;
            }
        }
        return true;
    }
    /**
     * Fields.
     *
     * @param \stored_file $file Current file.
     * @param \stdClass $cm Current module.
     * @param array $identity Current source identity.
     * @param \stdClass $course Current course.
     * @param array $filters Search values.
     * @return bool Result.
     */
    private static function fields(
        \stored_file $file,
        \stdClass $cm,
        array $identity,
        \stdClass $course,
        array $filters
    ): bool {
        $values = ['edition' => $identity['edition'], 'module' => $cm->modname,
            'shortname' => $course->shortname, 'mimetype' => $file->get_mimetype()];
        foreach ($values as $name => $value) {
            if (!empty($filters[$name]) && $filters[$name] !== $value) {
                return false;
            }
        }
        return !isset($filters['section']) || $filters['section'] === '' || (int)$filters['section'] === (int)$cm->section;
    }
    /**
     * Filename.
     *
     * @param \stored_file $file Current source file.
     * @param array $filters Search values.
     * @return bool Result.
     */
    private static function filename(\stored_file $file, array $filters): bool {
        if (empty($filters['q'])) {
            return true;
        }
        return \core_text::strpos(\core_text::strtolower($file->get_filename()), \core_text::strtolower($filters['q'])) !== false;
    }
}
