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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\output\navigation;
use local_multipicker\output\view;

admin_externalpage_setup('local_multipicker_profiles');
$profileid = optional_param('profileid', 0, PARAM_INT);
if ($profileid) {
    redirect(new moodle_url('/local/multipicker/admin/profile.php', ['profileid' => $profileid]));
}
$data = ['profiles' => [], 'newprofileurl' => (new moodle_url('/local/multipicker/admin/profile.php'))->out(false)];
foreach (
    $DB->get_records_sql(
        'SELECT p.*, r.revision FROM {local_multipicker} p
       LEFT JOIN {local_multipicker_revision} r ON r.id = p.activeid ORDER BY p.name'
    ) as $profile
) {
    $data['profiles'][] = [
        'name' => \local_multipicker\output\display::name($profile->name),
        'url' => (new moodle_url('/local/multipicker/admin/profile.php', ['profileid' => $profile->id]))->out(false),
        'status' => \local_multipicker\output\display::outcome($profile->enabled ? 'enabled' : 'disabled'),
        'revision' => $profile->revision,
    ];
}
$data['hasprofiles'] = (bool)$data['profiles'];
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
echo $PAGE->get_renderer('local_multipicker')->render(navigation::site('profiles'));
echo $PAGE->get_renderer('local_multipicker')->render(new view('profile_list', $data));
echo $OUTPUT->footer();
