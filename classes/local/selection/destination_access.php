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
 * CourseFiles native material import integration.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefiles\local\selection;

/**
 * Resolves the actual destination editing permission.
 */
final class destination_access {
    /**
     * Require reuse and native editing authority in the current or supplied destination context.
     *
     * @param int $courseid Destination course.
     * @param \context|null $context Trusted context, or the current Moodle page context.
     * @param string $field Actual native field when binding a control.
     */
    public static function require(int $courseid, ?\context $context = null, string $field = ''): void {
        global $PAGE;
        $context = $context ?? $PAGE->context;
        $coursecontext = $context ? $context->get_course_context(false) : false;
        if (!$coursecontext || (int)$coursecontext->instanceid !== $courseid) {
            $context = \context_course::instance($courseid);
        }
        require_capability('local/coursefiles:reuse', $context);
        if ($context->contextlevel === CONTEXT_MODULE) {
            $cm = get_coursemodule_from_id(null, $context->instanceid, $courseid, false, MUST_EXIST);
            if (
                $cm->modname === 'folder' && ($field === 'files_filemanager'
                    || ($field === '' && has_capability('mod/folder:managefiles', $context)))
            ) {
                require_capability('mod/folder:managefiles', $context);
                return;
            }
        }
        require_capability('moodle/course:manageactivities', $context);
    }
}
