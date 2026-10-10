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
 * Bulk migration configuration and saved previews.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (data_submitted()) {
    require_sesskey();
    $ids = optional_param_array('plans', [], PARAM_INT);
    $single = optional_param('deleteplan', 0, PARAM_INT);
    if ($single) {
        $ids = [$single];
    }
    \local_multipicker\local\migration\plan_history::delete_many($ids);
    redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}
$page = max(0, optional_param('page', 0, PARAM_INT));
$perpage = \local_multipicker\output\pagination::size();
$search = optional_param('q', '', PARAM_TEXT);
$state = optional_param('state', '', PARAM_ALPHA);
$conditions = ['1 = 1'];
$params = [];
$states = ['reviewable', 'blocked', 'queued', 'running', 'partial', 'completed', 'cancelled'];
if ($state !== '' && !in_array($state, $states, true)) {
    throw new invalid_parameter_exception('Unknown migration status');
}
if ($search !== '') {
    $conditions[] = $DB->sql_like('f.name', ':search', false);
    $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
}
if ($state !== '') {
    $conditions[] = 'COALESCE(r.state, p.state) = :state';
    $params['state'] = $state;
}
$from = ' FROM {local_multipicker_plan} p JOIN {local_multipicker} f ON f.id = p.profileid
    LEFT JOIN {local_multipicker_run} r ON r.id = (SELECT MAX(last.id) FROM {local_multipicker_run} last
        WHERE last.planid = p.id) WHERE ' . implode(' AND ', $conditions);
$total = $DB->count_records_sql('SELECT COUNT(1)' . $from, $params);
$page = \local_multipicker\output\pagination::clamp($total, $page, $perpage);
$data = ['records' => [], 'search' => $search, 'states' => [],
    'sesskey' => sesskey(), 'formurl' => $PAGE->url->out(false), 'newurl' => (new moodle_url($PAGE->url, ['new' => 1]))->out(false),
    'perpage' => $perpage, 'filterhelp' => $OUTPUT->help_icon('listfilters', 'local_multipicker'),
    'pagination' => \local_multipicker\output\pagination::render(
        $total,
        $page,
        $perpage,
        new moodle_url($PAGE->url, ['q' => $search, 'state' => $state])
    )];
foreach ($states as $value) {
    $data['states'][] = ['value' => $value, 'selected' => $state === $value,
        'label' => \local_multipicker\output\display::outcome($value)];
}
foreach (
    $DB->get_records_sql('SELECT p.id, p.profileid, p.uuid, p.configjson, p.timecreated, f.name,
    COALESCE(r.state, p.state) AS status' . $from . ' ORDER BY p.id DESC', $params, $page * $perpage, $perpage) as $plan
) {
    $document = json_decode($plan->configjson, true);
    $data['records'][] = ['name' => \local_multipicker\output\display::name($plan->name),
        'id' => $plan->id, 'candelete' => !in_array($plan->status, ['queued', 'running'], true),
        'reference' => $plan->id, 'date' => userdate($plan->timecreated),
        'source' => $document['sourceedition'] ?: implode(' / ', $document['filters']['source'] ?? []),
        'target' => $document['targetedition'] ?: implode(' / ', $document['filters']['target'] ?? []),
        'status' => \local_multipicker\output\display::outcome($plan->status),
        'editurl' => $plan->status === 'blocked' ? (new moodle_url('/local/multipicker/admin/migration.php', [
            'fromplan' => $plan->id, 'profileid' => $plan->profileid, 'step' => 'scope',
        ]))->out(false) : '',
        'url' => (new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $plan->id,
            'step' => in_array($plan->status, ['queued', 'running', 'partial', 'completed', 'cancelled'], true)
                ? 'execute' : '']))->out(false)];
}
$data['hasactions'] = (bool)array_filter($data['records'], static fn($row) => $row['editurl'] || $row['candelete']);
$PAGE->requires->js_call_amd('local_multipicker/administration', 'init');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
echo $PAGE->get_renderer('local_multipicker')->render(\local_multipicker\output\navigation::site('migration'));
echo $OUTPUT->render_from_template('local_multipicker/local/migration/list', $data);
echo $OUTPUT->footer();
