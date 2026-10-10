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
 * Canonical byte selection across overlapping public activity occurrences.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\local\selection;

/**
 * Keep one chosen source occurrence per native content hash with a bounded lookup.
 */
final class selected_content {
    /**
     * Read native identity for already authorized canonical file locators.
     *
     * @param array $values File selectors, with legacy folder locators ignored.
     * @return array Native records keyed by file selector.
     */
    public static function records(array $values): array {
        global $DB;
        $hashes = array_map(static fn($value) => substr($value, 5), array_values(array_filter(
            $values,
            static fn($value) => str_starts_with($value, 'file:')
        )));
        if (!$hashes) {
            return [];
        }
        [$in, $params] = $DB->get_in_or_equal($hashes, SQL_PARAMS_NAMED, 'selectedfile');
        $records = $DB->get_records_select('files', 'pathnamehash ' . $in, $params, '', 'pathnamehash, contenthash, filesize');
        $result = [];
        foreach ($values as $value) {
            if (isset($records[substr($value, 5)])) {
                $result[$value] = $records[substr($value, 5)];
            }
        }
        return $result;
    }

    /**
     * Preserve the first explicitly chosen occurrence for each set of bytes.
     *
     * @param array $values File selectors.
     * @return array Unique canonical selection, retaining legacy folder locators.
     */
    public static function unique(array $values): array {
        $records = self::records($values);
        $seen = [];
        $result = [];
        foreach ($values as $value) {
            if (str_starts_with($value, 'folder:')) {
                $result[] = $value;
            } else if (isset($records[$value]) && !isset($seen[$records[$value]->contenthash])) {
                $seen[$records[$value]->contenthash] = true;
                $result[] = $value;
            }
        }
        return $result;
    }
}
