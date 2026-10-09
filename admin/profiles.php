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
 * Profile list separate from editing.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_coursebridge\output\navigation;
use local_coursebridge\output\view;

admin_externalpage_setup('local_coursebridge_profiles');
$profileid = optional_param('profileid', 0, PARAM_INT);
if ($profileid) {
    redirect(new moodle_url('/local/coursebridge/admin/profile.php', ['profileid' => $profileid]));
}
$data = ['profiles' => [], 'newprofileurl' => (new moodle_url('/local/coursebridge/admin/profile.php'))->out(false)];
foreach (
    $DB->get_records_sql(
        'SELECT p.*, r.revision FROM {local_coursebridge} p
       LEFT JOIN {local_coursebridge_revision} r ON r.id = p.activeid ORDER BY p.name'
    ) as $profile
) {
    $data['profiles'][] = [
        'name' => $profile->name,
        'url' => (new moodle_url('/local/coursebridge/admin/profile.php', ['profileid' => $profile->id]))->out(false),
        'status' => \local_coursebridge\output\display::outcome($profile->enabled ? 'enabled' : 'disabled'),
        'revision' => $profile->revision,
    ];
}
$data['hasprofiles'] = (bool)$data['profiles'];
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_coursebridge'));
echo $PAGE->get_renderer('local_coursebridge')->render(navigation::site('profiles'));
echo $PAGE->get_renderer('local_coursebridge')->render(new view('profile_list', $data));
echo $OUTPUT->footer();
