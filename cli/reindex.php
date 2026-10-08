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
 * CLI course material reconciliation.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognised] = cli_get_params(['help' => false, 'courseid' => 0], ['h' => 'help']);
if ($options['help'] || $unrecognised) {
    cli_writeln("CourseBridge index refresh\n"
        . "--courseid=N  Refresh one course; omit to refresh all courses\n--help        Show help");
    exit($unrecognised ? 1 : 0);
}
\core\session\manager::set_user(get_admin());
if ($options['courseid']) {
    \local_coursebridge\local\indexer::refresh((int)$options['courseid']);
} else {
    $courses = $DB->get_recordset_select('course', 'id <> ?', [SITEID], 'id', 'id');
    try {
        foreach ($courses as $course) {
            \local_coursebridge\local\indexer::refresh($course->id);
            cli_writeln((string)$course->id);
        }
    } finally {
        $courses->close();
    }
}
