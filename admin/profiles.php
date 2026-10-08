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
 * Profile configuration and preview administration.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$profileid = optional_param('profileid', 0, PARAM_INT);
admin_externalpage_setup('local_coursebridge_profiles');
$form = new \local_coursebridge\form\profile_form(null, ['profileid' => $profileid]);
if ($data = $form->get_data()) {
    $profileid = \local_coursebridge\local\profile\profiles::save($data->configjson, $data->profileid);
    redirect(
        new moodle_url('/local/coursebridge/admin/profiles.php', ['profileid' => $profileid]),
        get_string('revisionactivated', 'local_coursebridge')
    );
}
if ($profileid) {
    [$profile, $revision] = \local_coursebridge\local\profile\profiles::load($profileid);
    $form->set_data(['profileid' => $profileid, 'configjson' => $revision->configjson]);
} else {
    $form->set_data(['configjson' => file_get_contents(__DIR__ . '/../examples/institutional-profile.json')]);
}
echo $OUTPUT->header();
$data = ['profiles' => [], 'newprofileurl' => (new moodle_url('/local/coursebridge/admin/profiles.php'))->out(false)];
foreach ($DB->get_records('local_coursebridge', [], 'name') as $profile) {
    $data['profiles'][] = [
        'name' => $profile->name,
        'url' => (new moodle_url('/local/coursebridge/admin/profiles.php', ['profileid' => $profile->id]))->out(false),
        'status' => \local_coursebridge\output\display::outcome($profile->enabled ? 'enabled' : 'disabled'),
        'revision' => $DB->get_field('local_coursebridge_revision', 'revision', ['id' => $profile->activeid]),
    ];
}
$data['hasprofiles'] = (bool)$data['profiles'];
echo $PAGE->get_renderer('local_coursebridge')->render(new \local_coursebridge\output\view('profile_list', $data));
echo $OUTPUT->heading(get_string('profileeditor', 'local_coursebridge'), 3);
$form->display();
echo $OUTPUT->footer();
