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
 * Configurable source and destination course dimensions.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

use local_multipicker\local\profile\profile_config;

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
        if (isset($rules->get_config()['relationship']['edition'])) {
            $choices['edition'] = get_string('edition', 'local_multipicker');
        }
        foreach ($rules->filter_definitions() as $key => $definition) {
            if (!isset($choices[$key])) {
                $choices[$key] = format_string($definition['label'], true, ['context' => \context_system::instance()]);
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
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        return $definitions[$key]['capture'];
    }
}
