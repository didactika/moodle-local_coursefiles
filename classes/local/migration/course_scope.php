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
 * Configurable source and destination course dimensions.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\profile\profile_config;

/**
 * Resolves the selected dimension from the exact profile revision.
 */
final class course_scope {
    /**
     * Return named captures available for source/destination matching.
     *
     * @param profile_config $rules Exact rules.
     * @return array Keys and display labels.
     */
    public static function choices(profile_config $rules): array {
        $choices = [];
        foreach ($rules->filter_definitions() as $key => $definition) {
            if (!isset($choices[$key])) {
                $choices[$key] = \local_filebank\output\display::label($definition['label']);
            }
        }
        return $choices;
    }

    /**
     * Resolve an edition or a configured filter to its named capture.
     *
     * @param profile_config $rules Exact rules.
     * @param string $key Dimension key.
     * @return string Capture name.
     */
    public static function capture(profile_config $rules, string $key): string {
        if ($key === 'edition' && isset($rules->get_config()['relationship']['edition'])) {
            return $rules->get_config()['relationship']['edition']['capture'];
        }
        $definitions = $rules->filter_definitions();
        if (!isset($definitions[$key])) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        return $definitions[$key]['capture'];
    }
}
