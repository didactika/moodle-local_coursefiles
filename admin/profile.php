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
 * Profile editor and unsaved rule preview.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_coursebridge\form\profile_form;
use local_coursebridge\output\navigation;
use local_coursebridge\output\profile_summary;
use local_coursebridge\output\view;

$profileid = optional_param('profileid', 0, PARAM_INT);
admin_externalpage_setup(
    'local_coursebridge_profiles',
    '',
    ['profileid' => $profileid],
    new moodle_url('/local/coursebridge/admin/profile.php', ['profileid' => $profileid])
);
$form = new profile_form(null, ['profileid' => $profileid]);
$listurl = new moodle_url('/local/coursebridge/admin/profiles.php');
if ($form->is_cancelled()) {
    redirect($listurl);
}
$summary = null;
if ($data = $form->get_data()) {
    $config = profile_form::document((array)$data);
    if (!empty($data->previewrules)) {
        $summary = profile_summary::data($config);
    } else {
        \local_coursebridge\local\profile\profiles::save(json_encode($config), $profileid);
        redirect(
            $listurl,
            get_string('revisionactivated', 'local_coursebridge'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}
if (!$form->is_submitted()) {
    if ($profileid) {
        $profile = $DB->get_record('local_coursebridge', ['id' => $profileid], '*', MUST_EXIST);
        $revision = $DB->get_record('local_coursebridge_revision', ['id' => $profile->activeid], '*', MUST_EXIST);
        $config = json_decode($revision->configjson, true);
    } else {
        $config = json_decode(file_get_contents(__DIR__ . '/../examples/institutional-profile.json'), true);
    }
    $form->set_data(['profileid' => $profileid, 'profilename' => $config['name'] ?? '',
        'configjson' => json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_coursebridge'));
$renderer = $PAGE->get_renderer('local_coursebridge');
echo $renderer->render(navigation::site('profiles'));
echo html_writer::link($listurl, get_string('backtoprofiles', 'local_coursebridge'), ['class' => 'btn btn-link mb-3']);
echo $OUTPUT->heading(get_string($profileid ? 'profileeditor' : 'newprofile', 'local_coursebridge'), 3);
echo html_writer::tag('p', get_string('profileeditorhelp', 'local_coursebridge'), ['class' => 'text-muted']);
if ($summary !== null) {
    echo $OUTPUT->notification(get_string('unsavedpreview', 'local_coursebridge'), 'info');
    echo $renderer->render(new view('profile_summary', $summary));
}
$form->display();
echo $OUTPUT->footer();
