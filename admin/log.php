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
 * Site action log.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_coursebridge_log');
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$entityuuid = optional_param('entityuuid', '', PARAM_RAW_TRIMMED);
$before = optional_param('before', 0, PARAM_INT);
$url = new moodle_url('/local/coursebridge/admin/log.php', ['action' => $action, 'entityuuid' => $entityuuid]);
$PAGE->set_url($url);
$page = \local_coursebridge\local\logging\log::page($action, $entityuuid, $before);
$options = [];
foreach (\local_coursebridge\local\logging\log::actions() as $name) {
    $options[] = ['value' => $name, 'label' => get_string('logaction_' . $name, 'local_coursebridge'),
        'selected' => $action === $name];
}
$userids = array_unique(array_filter(array_column($page['records'], 'userid')));
$users = $userids ? $DB->get_records_list('user', 'id', $userids) : [];
$records = [];
foreach ($page['records'] as $record) {
    $user = $users[$record->userid] ?? null;
    $records[] = [
        'date' => userdate($record->timecreated),
        'action' => in_array($record->action, \local_coursebridge\local\logging\log::actions(), true)
            ? get_string('logaction_' . $record->action, 'local_coursebridge') : $record->action,
        'user' => $user && !$user->deleted ? fullname($user)
            : get_string('logunknownuser', 'local_coursebridge'),
        'reference' => $record->entityuuid,
    ];
}
$nexturl = $page['nextbefore'] ? new moodle_url($url, ['before' => $page['nextbefore']]) : null;
$renderer = $PAGE->get_renderer('local_coursebridge');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_coursebridge'));
echo $PAGE->get_renderer('local_coursebridge')->render(\local_coursebridge\output\navigation::site('log'));
echo $renderer->render(new \local_coursebridge\output\view('log', [
    'formurl' => (new moodle_url('/local/coursebridge/admin/log.php'))->out(false),
    'options' => $options, 'entityuuid' => $entityuuid, 'records' => $records,
    'hasrecords' => (bool)$records, 'nexturl' => $nexturl ? $nexturl->out(false) : '',
    'clearurl' => (new moodle_url('/local/coursebridge/admin/log.php'))->out(false),
]));
echo $OUTPUT->footer();
