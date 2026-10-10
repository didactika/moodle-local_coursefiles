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
 * Authorized choices for native mixed material selection.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\local\catalog\catalog;

/**
 * Bounded native choice values; content groups show one explicitly labelled file origin.
 */
final class selection_options {
    /**
     * Parse and order a bounded mixed selection without accepting arbitrary JSON.
     *
     * @param array $values Native multiple-choice values.
     * @return array File/folder selectors.
     */
    public static function parse(array $values): array {
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
        }
        $values = array_values(array_unique($values));
        sort($values, SORT_STRING);
        if (count($values) > material_selection::MAX_FILES) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
        $items = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
            if (preg_match('/^file:([a-f0-9]{40})$/', $value, $match)) {
                $items[] = ['pathnamehash' => $match[1]];
            } else if (preg_match('/^folder:([1-9][0-9]*):([A-Za-z0-9+\/=]+)$/', $value, $match)) {
                $path = base64_decode($match[2], true);
                if (
                    $path === false || \core_text::strlen($path) > 255 || base64_encode($path) !== $match[2]
                        || (string)(int)$match[1] !== $match[1]
                ) {
                    throw new \moodle_exception('invalidselection', 'local_filebank');
                }
                $items[] = ['cmid' => (int)$match[1], 'filepath' => material_selection::path($path)];
            } else {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
        }
        return $items;
    }

    /**
     * Require an exact native filename without silently rewriting submitted text.
     *
     * @param string $filename Destination name.
     * @return string Validated name.
     */
    public static function filename(string $filename): string {
        if (
            $filename === '' || $filename === '.' || $filename === '..'
            || \core_text::strlen($filename) > 255 || clean_param($filename, PARAM_FILE) !== $filename
        ) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
        return $filename;
    }

    /**
     * Build one page of authorized choices and retain off-page selected values without disclosing source metadata.
     *
     * @param int $courseid Destination course.
     * @param int $profileid Relationship profile.
     * @param int $destinationcm Destination folder to exclude.
     * @param array $filters Search fields.
     * @param string $after Content cursor.
     * @param array $selected Previously selected values.
     * @return array Choices and next cursor.
     */
    public static function page(
        int $courseid,
        int $profileid,
        int $destinationcm,
        array $filters,
        string $after,
        array $selected
    ): array {
        self::parse($selected);
        $result = catalog::search($courseid, $profileid, $filters, $after, 50);
        $folders = [];
        $files = [];
        $limited = false;
        foreach ($result['assets'] as $asset) {
            $chosen = false;
            foreach ($asset['occurrences'] as $origin) {
                if ((int)$origin['cmid'] === $destinationcm) {
                    continue;
                }
                $label = $origin['shortname'] . ': ' . $origin['modulename'];
                if (!$chosen) {
                    $files['file:' . $origin['pathnamehash']] = get_string('batchfile', 'local_filebank')
                        . ': ' . $label . $origin['filepath'] . $origin['filename'];
                    $chosen = true;
                }
                if ($origin['module'] === 'folder') {
                    $path = $origin['filepath'];
                    do {
                        if (count($folders) >= 150) {
                            $limited = true;
                            break;
                        }
                        $value = 'folder:' . $origin['cmid'] . ':' . base64_encode($path);
                        $folders[$value] = get_string('batchfolder', 'local_filebank') . ': ' . $label . $path;
                        $parent = dirname(rtrim($path, '/'));
                        $path = $parent === '/' || $parent === '.' ? '/' : $parent . '/';
                    } while (
                        $origin['filepath'] !== '/'
                        && !isset($folders['folder:' . $origin['cmid'] . ':' . base64_encode($path)])
                    );
                }
            }
        }
        asort($folders, SORT_NATURAL | SORT_FLAG_CASE);
        asort($files, SORT_NATURAL | SORT_FLAG_CASE);
        $choices = $folders + $files;
        foreach ($selected as $index => $value) {
            if (!isset($choices[$value])) {
                $choices[$value] = get_string('batchretained', 'local_filebank', $index + 1);
            }
        }
        return ['choices' => $choices, 'next' => $result['next'], 'limited' => $limited];
    }
}
