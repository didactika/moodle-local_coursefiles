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
 * Site action log.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_multipicker_log');
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$entityuuid = optional_param('entityuuid', '', PARAM_RAW_TRIMMED);
$before = optional_param('before', 0, PARAM_INT);
$from = optional_param('from', '', PARAM_RAW_TRIMMED);
$until = optional_param('until', '', PARAM_RAW_TRIMMED);
$dates = [];
foreach (['from' => $from, 'until' => $until] as $key => $value) {
    if ($value === '') {
        $dates[$key] = 0;
        continue;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, \core_date::get_user_timezone_object());
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new invalid_parameter_exception('Invalid date filter');
    }
    $dates[$key] = ($key === 'until' ? $date->modify('+1 day') : $date)->getTimestamp();
}
$url = new moodle_url('/local/multipicker/admin/log.php', [
    'action' => $action, 'entityuuid' => $entityuuid, 'from' => $from, 'until' => $until, 'before' => $before,
]);
$PAGE->set_url($url);
$perpage = \local_multipicker\output\pagination::size();
$page = \local_multipicker\local\logging\log::page(
    $action,
    $entityuuid,
    $before,
    $perpage,
    $dates['from'],
    $dates['until'],
    max(0, optional_param('page', 0, PARAM_INT))
);
$PAGE->requires->js_call_amd('local_multipicker/administration', 'init');
$options = [];
foreach (\local_multipicker\local\logging\log::actions() as $name) {
    $options[] = ['value' => $name, 'label' => get_string('logaction_' . $name, 'local_multipicker'),
        'selected' => $action === $name];
}
$userids = array_unique(array_filter(array_column($page['records'], 'userid')));
$users = $userids ? $DB->get_records_list('user', 'id', $userids) : [];
$records = [];
foreach ($page['records'] as $record) {
    $user = $users[$record->userid] ?? null;
    $records[] = [
        'date' => userdate($record->timecreated),
        'action' => in_array($record->action, \local_multipicker\local\logging\log::actions(), true)
            ? get_string('logaction_' . $record->action, 'local_multipicker') : $record->action,
        'user' => $user && !$user->deleted ? fullname($user)
            : get_string('logunknownuser', 'local_multipicker'),
        'reference' => $record->entityuuid,
    ];
}
$nexturl = $page['nextbefore'] ? new moodle_url($url, ['before' => $page['nextbefore']]) : null;
$renderer = $PAGE->get_renderer('local_multipicker');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
echo $PAGE->get_renderer('local_multipicker')->render(\local_multipicker\output\navigation::site('log'));
echo $renderer->render(new \local_multipicker\output\view('log', [
    'formurl' => (new moodle_url('/local/multipicker/admin/log.php'))->out(false),
    'options' => $options, 'entityuuid' => $entityuuid, 'records' => $records,
    'perpage' => $perpage, 'filterhelp' => $OUTPUT->help_icon('listfilters', 'local_multipicker'),
    'pagination' => \local_multipicker\output\pagination::render($page['total'], $page['page'], $perpage, $url),
    'from' => $from, 'until' => $until, 'hasrecords' => (bool)$records, 'nexturl' => $nexturl ? $nexturl->out(false) : '',
    'clearurl' => (new moodle_url('/local/multipicker/admin/log.php'))->out(false),
]));
echo $OUTPUT->footer();
