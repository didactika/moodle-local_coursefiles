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
 * Small shared adapters for supported legacy runtimes.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\local;

/**

 * Keep PHP 8.0 support without installing global polyfills.

 */
final class compatibility {
    /**
     * Check for consecutive integer keys starting at zero.
     *
     * @param array $value Input array.
     * @return bool Consecutive integer keys starting at zero.
     */
    public static function is_list(array $value): bool {
        return array_keys($value) === array_keys(array_values($value));
    }
}
