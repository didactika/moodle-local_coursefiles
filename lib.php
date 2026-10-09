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
 * Course navigation integration.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add course material and policy entries.
 *
 * @param navigation_node $navigation Course settings.
 * @param stdClass $course Course.
 * @param context_course $context Course context.
 */
function local_coursebridge_extend_navigation_course($navigation, $course, $context): void {
    if (has_capability('local/coursebridge:browse', $context)) {
        $navigation->add(
            get_string('dashboard', 'local_coursebridge'),
            new moodle_url('/local/coursebridge/course/index.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM
        );
    } else if (has_capability('local/coursebridge:managecoursepolicy', $context)) {
        $navigation->add(
            get_string('coursepolicy', 'local_coursebridge'),
            new moodle_url('/local/coursebridge/course/policy.php', ['courseid' => $course->id]),
            navigation_node::TYPE_CUSTOM
        );
    }
}
