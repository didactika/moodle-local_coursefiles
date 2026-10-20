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
 * Import portable profiles using Moodle's native upload form.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_filebank_profiles', '', [], new moodle_url('/local/filebank/admin/profile_import.php'));
$listurl = new moodle_url('/local/filebank/admin/profiles.php');
$form = new \local_filebank\form\profile_import_form();
if ($form->is_cancelled()) {
    redirect($listurl);
}
if ($form->get_data()) {
    $ids = \local_filebank\local\profile\profile_bundle::import($form->get_file_content('bundle'));
    redirect(
        $listurl,
        get_string('profilesimported', 'local_filebank', count($ids)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_filebank'));
echo $PAGE->get_renderer('local_filebank')->render(\local_filebank\output\navigation::site('profiles'));
echo $OUTPUT->heading(get_string('importprofiles', 'local_filebank'), 3);
echo html_writer::tag('p', get_string('profileimporthelp', 'local_filebank'), ['class' => 'text-muted']);
echo html_writer::start_div('filebank-ui filebank-admin filebank-panel');
$form->display();
echo html_writer::end_div();
echo $OUTPUT->footer();
