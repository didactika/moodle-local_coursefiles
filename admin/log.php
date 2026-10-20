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
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_filebank_log');
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$entityuuid = optional_param('entityuuid', '', PARAM_ALPHANUMEXT);
$before = optional_param('before', 0, PARAM_INT);
$from = trim(optional_param('from', '', PARAM_TEXT));
$until = trim(optional_param('until', '', PARAM_TEXT));
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
$url = new moodle_url('/local/filebank/admin/log.php', [
    'action' => $action, 'entityuuid' => $entityuuid, 'from' => $from, 'until' => $until, 'before' => $before,
]);
$PAGE->set_url($url);
$asked = \local_filebank\output\pagination::size();
$perpage = $asked === \local_filebank\output\pagination::ALL ? \local_filebank\output\pagination::MAX_ALL : $asked;
$page = \local_filebank\local\logging\log::page(
    $action,
    $entityuuid,
    $before,
    $perpage,
    $dates['from'],
    $dates['until'],
    max(0, optional_param('page', 0, PARAM_INT))
);
$rows = \local_filebank\output\pagination::rows($asked, $page['total']);
if ($rows !== $perpage) {
    $perpage = $rows;
    $page = \local_filebank\local\logging\log::page(
        $action,
        $entityuuid,
        $before,
        $perpage,
        $dates['from'],
        $dates['until'],
        max(0, optional_param('page', 0, PARAM_INT))
    );
}
$PAGE->requires->js_call_amd('local_filebank/administration', 'init');
$PAGE->requires->js_call_amd('local_filebank/local/admin/dates', 'init');
// The stylesheet of the calendar has to be in the page before the header goes out.
$PAGE->requires->css(new moodle_url('/local/filebank/lib/daterangepicker/daterangepicker.css'));
$options = [];
foreach (\local_filebank\local\logging\log::actions() as $name) {
    $options[] = ['value' => $name, 'label' => get_string('logaction_' . $name, 'local_filebank'),
        'selected' => $action === $name];
}
$userids = array_unique(array_filter(array_column($page['records'], 'userid')));
$users = $userids ? $DB->get_records_list('user', 'id', $userids) : [];
$records = [];
foreach ($page['records'] as $record) {
    $user = $users[$record->userid] ?? null;
    $records[] = [
        'date' => userdate($record->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        'action' => in_array($record->action, \local_filebank\local\logging\log::actions(), true)
            ? get_string('logaction_' . $record->action, 'local_filebank') : $record->action,
        'user' => $user && !$user->deleted ? fullname($user)
            : get_string('logunknownuser', 'local_filebank'),
        'reference' => $record->entityuuid,
    ];
}
$active = [];
$names = ['action' => 'logaction', 'from' => 'logfrom', 'until' => 'loguntil'];
$labels = [];
foreach ($options as $option) {
    $labels[$option['value']] = $option['label'];
}
$filters = ['action' => $action, 'entityuuid' => $entityuuid, 'from' => $from, 'until' => $until];
foreach ($filters as $key => $value) {
    if ($value === '' || !isset($names[$key])) {
        continue;
    }
    $without = array_filter(array_diff_key($filters, [$key => true]), static fn($other) => $other !== '');
    $active[] = [
        'label' => get_string('filterchip', 'local_filebank', (object)[
            'name' => get_string($names[$key], 'local_filebank'),
            'value' => $key === 'action' ? ($labels[$value] ?? $value) : $value,
        ]),
        'removeurl' => (new moodle_url('/local/filebank/admin/log.php', $without))->out(false),
    ];
}
$nexturl = $page['nextbefore'] ? new moodle_url($url, ['before' => $page['nextbefore']]) : null;
$renderer = $PAGE->get_renderer('local_filebank');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_filebank'));
echo $PAGE->get_renderer('local_filebank')->render(\local_filebank\output\navigation::site('log'));
echo $renderer->render(new \local_filebank\output\view('log', [
    'formurl' => (new moodle_url('/local/filebank/admin/log.php'))->out(false),
    'options' => $options, 'entityuuid' => $entityuuid, 'records' => $records,
    'perpage' => \local_filebank\output\pagination::value($perpage, $page['total']),
    'pagination' => \local_filebank\output\pagination::render($page['total'], $page['page'], $perpage, $url),
    'activefilters' => $active, 'activecount' => count($active), 'hasactive' => (bool)$active,
    'lang' => current_language(),
    'fromlabel' => $dates['from'] ? userdate($dates['from'], get_string('strftimedate', 'langconfig')) : '',
    'untillabel' => $dates['until'] ? userdate($dates['until'] - DAYSECS, get_string('strftimedate', 'langconfig')) : '',
    'from' => $from, 'until' => $until, 'hasrecords' => (bool)$records, 'nexturl' => $nexturl ? $nexturl->out(false) : '',
    'clearurl' => (new moodle_url('/local/filebank/admin/log.php'))->out(false),
]));
echo $OUTPUT->footer();
