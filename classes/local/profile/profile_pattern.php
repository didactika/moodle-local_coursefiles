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
 * Bounded declarative course relationship rules.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\profile;

/**
 * Evaluate bounded PCRE expressions and extract only named captures.
 */
final class profile_pattern {
    /**
     * Match bounded PCRE.
     *
     * @param string $pattern Delimited pattern.
     * @param string $value Input.
     * @return array|null Named captures.
     */
    public static function match(string $pattern, string $value): ?array {
        if (strlen($value) > 8192 || !in_array(substr($pattern, 0, 1), ['~', '#', '/'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        $bounded = $pattern[0] . '(*LIMIT_MATCH=100000)(*LIMIT_DEPTH=1000)' . substr($pattern, 1);
        $result = @preg_match($bounded, $value, $matches, PREG_UNMATCHED_AS_NULL);
        if ($result === false) {
            throw new \moodle_exception('regexfailed', 'local_filebank');
        }
        return $result ? array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY) : null;
    }
}
