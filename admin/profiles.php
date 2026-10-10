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
 * Profile list separate from editing.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\output\navigation;
use local_multipicker\output\view;

admin_externalpage_setup('local_multipicker_profiles');
if (data_submitted()) {
    require_sesskey();
    $action = optional_param('action', '', PARAM_ALPHA);
    $ids = optional_param_array('profiles', [], PARAM_INT);
    $rowaction = optional_param('rowaction', '', PARAM_RAW);
    if ($rowaction !== '') {
        if (!preg_match('/^(enable|disable|delete):[1-9][0-9]*$/', $rowaction)) {
            throw new invalid_parameter_exception('Invalid profile action');
        }
        [$action, $id] = explode(':', $rowaction, 2);
        $ids = [(int)$id];
    }
    if (in_array($action, ['enable', 'disable', 'delete'], true)) {
        \local_multipicker\local\profile\profiles::change_state($ids, $action);
        redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($action !== 'export' && $action !== 'exportall') {
        throw new invalid_parameter_exception('Unknown profile action');
    }
    $json = \local_multipicker\local\profile\profile_bundle::export(
        optional_param_array('profiles', [], PARAM_INT),
        $action === 'exportall'
    );
    require_once($CFG->libdir . '/filelib.php');
    send_file($json, 'multipicker-profiles.json', 0, 0, true, true, 'application/json');
}
$page = max(0, optional_param('page', 0, PARAM_INT));
$perpage = \local_multipicker\output\pagination::size();
$search = optional_param('q', '', PARAM_TEXT);
$profileid = optional_param('profileid', 0, PARAM_INT);
if ($profileid) {
    redirect(new moodle_url('/local/multipicker/admin/profile.php', ['profileid' => $profileid]));
}
$data = ['contextid' => context_system::instance()->id, 'profiles' => [],
    'newprofileurl' => (new moodle_url('/local/multipicker/admin/profile.php'))->out(false),
    'importurl' => (new moodle_url('/local/multipicker/admin/profile_import.php'))->out(false),
    'filterhelp' => $OUTPUT->help_icon('listfilters', 'local_multipicker'),
    'actionurl' => $PAGE->url->out(false), 'search' => $search, 'sesskey' => sesskey(), 'perpage' => $perpage];
$condition = 'p.deleted = 0';
$params = [];
if ($search !== '') {
    $condition .= ' AND ' . $DB->sql_like('p.name', ':search', false);
    $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
}
$total = $DB->count_records_sql('SELECT COUNT(1) FROM {local_multipicker} p WHERE ' . $condition, $params);
$page = \local_multipicker\output\pagination::clamp($total, $page, $perpage);
$data['pagination'] = \local_multipicker\output\pagination::render(
    $total,
    $page,
    $perpage,
    new moodle_url('/local/multipicker/admin/profiles.php', ['q' => $search])
);
foreach (
    $DB->get_records_sql(
        'SELECT p.*, r.revision, r.timecreated, r.configjson FROM {local_multipicker} p
       LEFT JOIN {local_multipicker_revision} r ON r.id = p.activeid WHERE ' . $condition . ' ORDER BY p.name, p.id',
        $params,
        $page * $perpage,
        $perpage
    ) as $profile
) {
    $config = json_decode($profile->configjson, true);
    $labels = [];
    foreach ((new \local_multipicker\local\profile\profile_config($config))->filter_definitions() as $definition) {
        $labels[] = \local_multipicker\output\display::label($definition['label']);
    }
    $data['profiles'][] = [
        'grouping' => $config['relationship']['family_key'], 'filters' => implode(' · ', $labels),
        'historyurl' => (new moodle_url('/local/multipicker/admin/profile_history.php', ['profileid' => $profile->id]))->out(false),
        'id' => $profile->id,
        'name' => \local_multipicker\output\display::name($profile->name),
        'url' => (new moodle_url('/local/multipicker/admin/profile.php', ['profileid' => $profile->id]))->out(false),
        'status' => \local_multipicker\output\display::outcome($profile->enabled ? 'enabled' : 'disabled'),
        'revision' => $profile->revision, 'enabled' => (bool)$profile->enabled,
        'date' => userdate($profile->timecreated),
    ];
}
$data['hasprofiles'] = (bool)$data['profiles'];
$PAGE->requires->js_call_amd('local_multipicker/administration', 'init');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
echo $PAGE->get_renderer('local_multipicker')->render(navigation::site('profiles'));
echo $PAGE->get_renderer('local_multipicker')->render(new view('profile_list', $data));
echo $OUTPUT->footer();
