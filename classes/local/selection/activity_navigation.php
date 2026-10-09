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
 * Native activity navigation entry for mixed selection.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

/**
 * Links authorized Folder/File activities to their existing settings form.
 */
final class activity_navigation {
    /**
     * Add one activity settings entry without replacing native editing controllers.
     *
     * @param \settings_navigation $navigation Native settings tree.
     * @param \context $context Current navigation context.
     */
    public static function extend(\settings_navigation $navigation, \context $context): void {
        global $PAGE, $DB;
        if (
            $context->contextlevel !== CONTEXT_MODULE || !$PAGE->cm
                || (int)$PAGE->cm->id !== (int)$context->instanceid
                || !in_array($PAGE->cm->modname, ['folder', 'resource'], true)
        ) {
            return;
        }
        $coursecontext = $context->get_course_context();
        if (
            !has_capability('moodle/course:manageactivities', $context)
                || !has_capability('local/coursebridge:browse', $coursecontext)
                || !has_capability('local/coursebridge:reuse', $coursecontext)
                || !$DB->record_exists('local_coursebridge', ['enabled' => 1])
        ) {
            return;
        }
        $parent = $navigation->find('modulesettings', \navigation_node::TYPE_SETTING);
        if (!$parent || $parent->get('coursebridge_reuse')) {
            return;
        }
        $parent->add(
            get_string('batchheading', 'local_coursebridge'),
            new \moodle_url('/course/modedit.php', ['update' => $PAGE->cm->id, 'return' => 1, 'coursebridge' => 1], 'id_cbheader'),
            \navigation_node::TYPE_SETTING,
            null,
            'coursebridge_reuse'
        );
    }
}
