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
 * Profile editor and unsaved rule preview.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_filebank\form\profile_form;
use local_filebank\output\navigation;
use local_filebank\output\profile_summary;
use local_filebank\output\view;

$profileid = optional_param('profileid', 0, PARAM_INT);
admin_externalpage_setup(
    'local_filebank_profiles',
    '',
    ['profileid' => $profileid],
    new moodle_url('/local/filebank/admin/profile.php', ['profileid' => $profileid])
);
if ($profileid) {
    $profile = $DB->get_record('local_filebank', ['id' => $profileid, 'deleted' => 0], '*', MUST_EXIST);
    $revision = $DB->get_record('local_filebank_revision', ['id' => $profile->activeid], '*', MUST_EXIST);
    $config = json_decode($revision->configjson, true);
} else {
    $config = [];
}
$definitions = $config ? (new \local_filebank\local\profile\profile_config($config))->filter_definitions() : [];
$form = new profile_form(null, ['profileid' => $profileid, 'config' => $config, 'filtercount' => max(2, count($definitions)),
    'sources' => $config['publication']['allowed_sources'] ?? []]);
$listurl = new moodle_url('/local/filebank/admin/profiles.php');
if ($form->is_cancelled()) {
    redirect($listurl);
}
$summary = null;
if ($data = $form->get_data()) {
    $config = profile_form::document((array)$data, $config);
    if (!empty($data->previewrules)) {
        $summary = profile_summary::data($config, false);
    } else {
        \local_filebank\local\profile\profiles::save(json_encode($config), $profileid);
        redirect(
            $listurl,
            get_string('revisionactivated', 'local_filebank'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}
if (!$form->is_submitted()) {
    $defaults = ['profileid' => $profileid, 'profilename' => $config['name'] ?? '',
        'editmode' => !$config || count($config['relationship']['parsers']) === 1 ? 'guided' : 'advanced',
        'coursefield' => $config['relationship']['parsers'][0]['field'] ?? 'idnumber',
        'coursepattern' => $config['relationship']['parsers'][0]['pattern'] ?? '',
        'familytemplate' => $config['relationship']['family_key'] ?? '',
        'configjson' => $config ? json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''];
    foreach ($definitions as $key => $definition) {
        $defaults['filtercapture'][] = $definition['capture'];
        $defaults['filterlabel'][] = $definition['label'];
        $defaults['filteroptional'][] = !empty($definition['optional']);
        $defaults['filterkey'][] = $key;
    }
    $form->set_data($defaults + ($config ? \local_filebank\local\profile\guided_config::defaults($config)
        : ['displaytemplate' => '', 'pairdimensions' => '', 'categoryids' => []]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_filebank'));
$renderer = $PAGE->get_renderer('local_filebank');
echo $renderer->render(navigation::site('profiles'));
echo $OUTPUT->heading(get_string($profileid ? 'profileeditor' : 'newprofile', 'local_filebank'), 3);
if ($summary !== null) {
    echo $OUTPUT->notification(get_string('unsavedpreview', 'local_filebank'), 'info');
    echo $renderer->render(new view('profile_summary', $summary));
}
echo html_writer::start_div('filebank-admin filebank-panel');
$form->display();
echo html_writer::end_div();
echo $OUTPUT->footer();
