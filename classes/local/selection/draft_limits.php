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
 * Reviewed batches for existing native form drafts.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\local\catalog\material_source;
use local_filebank\local\migration\copy_decision;
use local_filebank\local\policy\author_policy;
use local_filebank\local\profile\profiles;

/**
 * Validate complete native upload contracts and enforce each imported leaf against them.
 */
final class draft_limits {
    /**
     * Reject incomplete control contracts rather than falling back to unlimited uploads.
     *
     * @param array $limits Server-side options.
     */
    public static function contract(array $limits): void {

        foreach (['maxfiles', 'maxbytes', 'areamaxbytes'] as $name) {
            if (!isset($limits[$name]) || !is_int($limits[$name]) || $limits[$name] < -1) {
                throw new \moodle_exception('batchlimit', 'local_filebank');
            }
        }
        if (
            !isset($limits['subdirs'], $limits['accepted_types'])
                || !in_array($limits['subdirs'], [true, false, 0, 1], true)
                || !is_array($limits['accepted_types']) || !$limits['accepted_types']
        ) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
        self::types($limits['accepted_types']);
    }

    /**
     * Leaf.
     *
     * @param \stored_file $file File.
     * @param array $limits Limits.
     * @param string $path Path.
     * @param string $filename Filename.
     */
    public static function leaf(\stored_file $file, array $limits, string $path, string $filename): void {
        if (!$limits['subdirs'] && $path !== '/') {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
        if (
            !in_array('*', $limits['accepted_types'], true)
                && (!file_extension_in_typegroup($filename, $limits['accepted_types'], true)
                    || !file_extension_in_typegroup($file->get_filename(), $limits['accepted_types'], true))
        ) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
        if ($limits['maxbytes'] > 0 && $file->get_filesize() > $limits['maxbytes']) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
    }

    /**
     * Types.
     *
     * @param array $types Types.
     */
    private static function types(array $types): void {
        foreach ($types as $type) {
            if (!is_string($type) || $type === '') {
                throw new \moodle_exception('batchlimit', 'local_filebank');
            }
        }
    }

    /**
     * Selection.
     *
     * @param array $files Files.
     * @param array $limits Limits.
     */
    public static function selection(array $files, array $limits): void {
        if (count($files) > ($limits['selectionlimit'] ?? material_selection::MAX_FILES)) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
    }

    /**
     * Renames.
     *
     * @param array $files Files.
     * @param mixed $renames Submitted destination names.
     */
    public static function renames(array $files, $renames): void {
        if (!is_array($renames) || count($renames) > material_selection::MAX_FILES || array_diff_key($renames, $files)) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
    }

    /**
     * Total.
     *
     * @param array $limits Limits.
     * @param int $count Count.
     * @param int $bytes Bytes.
     */
    public static function total(array $limits, int $count, int $bytes): void {
        if (
            ($limits['maxfiles'] >= 0 && $count > $limits['maxfiles'])
                || ($limits['areamaxbytes'] >= 0 && $bytes > $limits['areamaxbytes'])
        ) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
    }
}
