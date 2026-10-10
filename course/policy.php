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
 * Native course material policy presentation.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_coursefiles\local\policy\author_policy;
use local_coursefiles\output\display;
use local_coursefiles\output\view;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/coursefiles:managecoursepolicy', $context);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/local/coursefiles/course/policy.php', ['courseid' => $courseid]);
$PAGE->set_title(get_string('coursepolicy', 'local_coursefiles'));
$PAGE->set_heading(format_string($course->fullname));
if (data_submitted()) {
    require_sesskey();
    author_policy::save($courseid, required_param('policy', PARAM_ALPHANUMEXT));
    redirect($PAGE->url, get_string('policyupdated', 'local_coursefiles'));
}
$locked = author_policy::locked($courseid);
$data = [
    'policyhelp' => $OUTPUT->help_icon('author_policy', 'local_coursefiles'),
    'actionurl' => $PAGE->url->out(false), 'sesskey' => sesskey(), 'courseid' => $courseid,
    'locked' => $locked,
    'effectivelabel' => get_string($locked ? 'preserve_locked' : 'editable_with_provenance', 'local_coursefiles'),
    'canoverride' => get_config('local_coursefiles', 'author_course_override') === 'tighten_only',
    'choices' => display::options([
        'inherit' => get_string('inherit', 'local_coursefiles'),
        'preserve_locked' => get_string('preserve_locked', 'local_coursefiles'),
    ], $DB->get_field('local_coursefiles_policy', 'policy', ['courseid' => $courseid]) ?: 'inherit'),
    'catalogurl' => (new moodle_url('/local/coursefiles/course/index.php', ['courseid' => $courseid]))->out(false),
];
echo $OUTPUT->header();
echo $PAGE->get_renderer('local_coursefiles')->render(\local_coursefiles\output\navigation::course('policy', $courseid));
echo $PAGE->get_renderer('local_coursefiles')->render(new view('policy', $data));
echo $OUTPUT->footer();
