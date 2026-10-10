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
 * Dynamic native picker controls from profile capture definitions.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\output;

use local_coursefiles\local\profile\profiles;

/**

 * Generates filter controls without enumerating all courses or exposing source metadata.

 */
final class capture_filters {
    /**
     * Export the current profile's labels and values; suggestions come from the authorized page only.
     *
     * @param int $profileid Selected profile.
     * @param array $selected Current keyed values.
     * @param array $suggestions Authorized current-page values.
     * @return array Native text controls.
     */
    public static function data(int $profileid, array $selected, array $suggestions = []): array {
        [, , $rules] = profiles::load($profileid);
        $controls = [];
        foreach ($rules->filter_definitions() as $name => $definition) {
            $values = array_values($suggestions[$name] ?? []);
            natsort($values);
            $controls[] = ['key' => $name, 'id' => 'mp-filter-' . $name,
                'label' => display::label($definition['label']), 'value' => $selected[$name] ?? '',
                'suggestions' => array_map(static fn($value) => ['value' => $value], array_slice($values, 0, 100))];
        }
        return $controls;
    }
}
