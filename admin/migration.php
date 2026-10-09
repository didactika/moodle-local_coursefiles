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
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_coursebridge\output\display;
use local_coursebridge\output\navigation;
use local_coursebridge\output\profile_summary;
use local_coursebridge\output\view;

admin_externalpage_setup('local_coursebridge_migration');
$planid = optional_param('planid', 0, PARAM_INT);
if ($planid) {
    redirect(new moodle_url('/local/coursebridge/admin/migration_review.php', ['planid' => $planid]));
}
$profiles = $DB->get_records('local_coursebridge', ['enabled' => 1], 'name');
$profileid = optional_param('profileid', 0, PARAM_INT);
if (!$profileid && $profiles) {
    $profileid = (int)reset($profiles)->id;
}
$form = null;
$data = ['profiles' => [], 'hasprofiles' => (bool)$profiles, 'recent' => [],
    'actionurl' => (new moodle_url('/local/coursebridge/admin/migration.php'))->out(false),
    'canmanageprofiles' => has_capability('local/coursebridge:manageprofiles', context_system::instance()),
    'profilesurl' => (new moodle_url('/local/coursebridge/admin/profiles.php'))->out(false)];
foreach ($profiles as $profile) {
    $data['profiles'][] = ['value' => $profile->id, 'label' => $profile->name, 'selected' => $profile->id == $profileid];
}
if ($profileid) {
    [$profile, $revision, $rules] = \local_coursebridge\local\profile\profiles::load($profileid);
    $config = $rules->get_config();
    $data['summary'] = profile_summary::data($config) + ['revision' => $revision->revision];
    if ($data['canmanageprofiles']) {
        $data['summary']['editurl'] = (new moodle_url(
            '/local/coursebridge/admin/profile.php',
            ['profileid' => $profileid]
        ))->out(false);
    }
    $editions = $DB->get_fieldset_sql(
        'SELECT DISTINCT edition FROM {local_coursebridge_course} WHERE revisionid = ? AND edition IS NOT NULL',
        [$revision->id]
    );
    $editions = array_values(array_filter($editions, static fn($edition) => $edition !== ''));
    natsort($editions);
    $data['haseditions'] = (bool)$editions;
    $form = new \local_coursebridge\form\migration_form(null, ['profileid' => $profileid, 'editions' => $editions,
        'ordinal' => ($config['migration']['section_match']['strategy'] ?? 'ordinal') === 'ordinal']);
    if ($submitted = $form->get_data()) {
        if ((int)$submitted->profileid !== $profileid) {
            throw new invalid_parameter_exception('The selected profile changed');
        }
        $plan = \local_coursebridge\local\migration\migration_planner::preview(
            $profileid,
            $submitted->sourceedition,
            $submitted->targetedition,
            !empty($submitted->layoutconfirmed)
        );
        redirect(new moodle_url('/local/coursebridge/admin/migration_review.php', ['planid' => $plan->id]));
    }
    if (!$form->is_submitted()) {
        $form->set_data(['sourceedition' => optional_param('sourceedition', '', PARAM_TEXT),
            'targetedition' => optional_param('targetedition', '', PARAM_TEXT)]);
    }
}
foreach (
    $DB->get_records_sql(
        'SELECT p.id, p.timecreated, p.state, f.name FROM {local_coursebridge_plan} p
       JOIN {local_coursebridge} f ON f.id = p.profileid ORDER BY p.id DESC',
        [],
        0,
        10
    ) as $plan
) {
    $data['recent'][] = ['name' => $plan->name, 'date' => userdate($plan->timecreated),
        'state' => display::outcome($plan->state),
        'url' => (new moodle_url('/local/coursebridge/admin/migration_review.php', ['planid' => $plan->id]))->out(false)];
}
$data['hasrecent'] = (bool)$data['recent'];
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_coursebridge'));
$renderer = $PAGE->get_renderer('local_coursebridge');
echo $renderer->render(navigation::site('migration'));
echo $renderer->render(new view('migration_setup', $data));
if ($form !== null) {
    echo $OUTPUT->heading(get_string('migrationstep_configure', 'local_coursebridge'), 3);
    $form->display();
}
echo $OUTPUT->footer();
