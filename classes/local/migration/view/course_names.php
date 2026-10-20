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
 * Course names for the migration screens.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration\view;

/**
 * Looks up the short name of the courses that a page of a migration mentions, once for each course, in one query.
 */
final class course_names {
    /**
     * @var string[] The names that were already read, by course.
     */
    private array $names = [];

    /**
     * Read the names of some courses in one query.
     *
     * @param int[] $courseids The courses that the page mentions.
     */
    public function load(array $courseids): void {
        global $DB;
        $missing = array_values(array_diff(array_unique(array_map('intval', $courseids)), array_keys($this->names)));
        if (!$missing) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($missing, SQL_PARAMS_NAMED, 'course');
        $found = $DB->get_records_select('course', 'id ' . $insql, $params, '', 'id, shortname');
        foreach ($missing as $courseid) {
            $this->names[$courseid] = isset($found[$courseid])
                ? \local_filebank\output\display::plain($found[$courseid]->shortname)
                : get_string('unknowncourse', 'local_filebank');
        }
    }

    /**
     * The name of a course that was loaded.
     *
     * @param int $courseid The course.
     * @return string Its short name, or a placeholder when the course no longer exists.
     */
    public function name(int $courseid): string {
        if (!isset($this->names[$courseid])) {
            $this->load([$courseid]);
        }
        return $this->names[$courseid];
    }
}
