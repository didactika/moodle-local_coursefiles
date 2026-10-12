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
 * FileBank native material import integration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\output\display;

/**
 * Normalize bounded chooser actions without accepting native form authority from the browser.
 */
final class browser_input {
    /**
     * Parse.
     *
     * @param array $raw Raw.
     * @return array Result.
     */
    public static function parse(array $raw): array {
        $input = [];
        foreach (['q', 'edition', 'shortname', 'module', 'mimetype', 'layout', 'mode'] as $name) {
            if (!is_string($raw[$name] ?? '') || strlen($raw[$name] ?? '') > 1024) {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
            $input[$name] = clean_param($raw[$name] ?? '', PARAM_TEXT);
        }
        $input['mode'] = $input['mode'] ?: 'paths';
        $input['profileid'] = clean_param($raw['profileid'] ?? 0, PARAM_INT);
        [$input['items'], $input['captures']] = self::lists($raw);
        $input['toggleitem'] = clean_param($raw['toggleitem'] ?? '', PARAM_RAW);
        $input['removeitem'] = clean_param($raw['removeitem'] ?? '', PARAM_RAW);
        $input['destination'] = material_selection::path($raw['destination'] ?? '/');
        foreach (['search', 'refresh', 'clear', 'clearfilters', 'selectpage', 'selectall', 'review'] as $name) {
            $input[$name] = clean_param($raw[$name] ?? false, PARAM_BOOL);
        }
        foreach (['openfolder', 'previouspage', 'next'] as $name) {
            if (array_key_exists($name, $raw)) {
                $input[$name] = clean_param($raw[$name], PARAM_RAW);
            }
        }
        return $input;
    }

    /**
     * Lists.
     *
     * @param array $raw Raw.
     * @return array Result.
     */
    private static function lists(array $raw): array {
        $items = $raw['items'] ?? [];
        $captures = $raw['captures'] ?? [];
        if (
            !is_array($items) || count($items) > material_selection::MAX_FILES || !is_array($captures)
                || count($captures) > 9 || array_filter($items, static fn($item) => !is_string($item))
                || array_filter($captures, static fn($value) => !is_string($value) || strlen($value) > 255)
        ) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
        $input['items'] = clean_param_array($items, PARAM_RAW);
        $input['captures'] = clean_param_array($captures, PARAM_TEXT);
        return [$input['items'], $input['captures']];
    }
}
