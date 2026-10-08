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
 * Scheduled cursor-based catalog reconciliation.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\task;

/**
 * Revisits at most one hundred courses per scheduled run.
 */
final class reconcile_catalog extends \core\task\scheduled_task {
    /**
     * Human-readable task name.
     *
     * @return string Name.
     */
    public function get_name(): string {
        return get_string('reconcilecatalog', 'local_coursebridge');
    }

    /**
     * Refresh the next bounded course slice.
     */
    public function execute(): void {
        global $DB;
        $after = (int)get_config('local_coursebridge', 'reconcile_cursor');
        $courses = $DB->get_records_select('course', 'id > ? AND id <> ?', [$after, SITEID], 'id', 'id', 0, 100);
        foreach ($courses as $course) {
            \local_coursebridge\local\catalog\indexer::refresh($course->id);
            set_config('reconcile_cursor', $course->id, 'local_coursebridge');
        }
        if (count($courses) < 100) {
            set_config('reconcile_cursor', 0, 'local_coursebridge');
        }
        $DB->delete_records_select(
            'local_coursebridge_file',
            'NOT EXISTS (SELECT 1 FROM {course} c WHERE c.id = {local_coursebridge_file}.courseid)'
        );
        $DB->delete_records_select(
            'local_coursebridge_course',
            'NOT EXISTS (SELECT 1 FROM {course} c WHERE c.id = {local_coursebridge_course}.courseid)'
        );
    }
}
