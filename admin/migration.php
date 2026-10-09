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

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\output\display;
use local_multipicker\output\navigation;
use local_multipicker\output\profile_summary;
use local_multipicker\output\view;

admin_externalpage_setup('local_multipicker_migration');
$planid = optional_param('planid', 0, PARAM_INT);
if ($planid) {
    redirect(new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $planid]));
}
$profiles = $DB->get_records('local_multipicker', ['enabled' => 1], 'name');
$profileid = optional_param('profileid', 0, PARAM_INT);
$step = optional_param('step', 'profile', PARAM_ALPHA);
if (!in_array($step, ['profile', 'scope'], true)) {
    throw new invalid_parameter_exception('Unknown migration step');
}
if (!$profileid && $profiles) {
    $profileid = (int)reset($profiles)->id;
}
$data = [];
$form = null;
$data = ['profiles' => [], 'hasprofiles' => (bool)$profiles, 'recent' => [],
    'actionurl' => (new moodle_url('/local/multipicker/admin/migration.php'))->out(false),
    'canmanageprofiles' => has_capability('local/multipicker:manageprofiles', context_system::instance()),
    'profilesurl' => (new moodle_url('/local/multipicker/admin/profiles.php'))->out(false)];
foreach ($profiles as $profile) {
    $data['profiles'][] = ['value' => $profile->id, 'label' => display::name($profile->name),
        'selected' => $profile->id == $profileid];
}
if ($profileid && $step === 'scope') {
    [$profile, $revision, $rules] = \local_multipicker\local\profile\profiles::load($profileid);
    $config = $rules->get_config();
    $data['summary'] = profile_summary::data($config) + ['revision' => $revision->revision];
    if ($data['canmanageprofiles']) {
        $data['summary']['editurl'] = (new moodle_url(
            '/local/multipicker/admin/profile.php',
            ['profileid' => $profileid]
        ))->out(false);
    }
    $editions = $DB->get_fieldset_sql(
        'SELECT DISTINCT edition FROM {local_multipicker_course} WHERE revisionid = ? AND edition IS NOT NULL',
        [$revision->id]
    );
    $editions = array_values(array_filter($editions, static fn($edition) => $edition !== ''));
    natsort($editions);
    $data['haseditions'] = (bool)$editions;
    $dimensions = \local_multipicker\local\migration\course_scope::choices($rules);
    $data['hasscope'] = (bool)$dimensions;
    $form = $dimensions ? new \local_multipicker\form\migration_form(null, [
        'profileid' => $profileid, 'dimensions' => $dimensions, 'editions' => $editions,
        'ordinal' => ($config['migration']['section_match']['strategy'] ?? 'ordinal') === 'ordinal']) : null;
    if ($form && ($submitted = $form->get_data())) {
        if ((int)$submitted->profileid !== $profileid) {
            throw new invalid_parameter_exception('The selected profile changed');
        }
        $plan = \local_multipicker\local\migration\migration_planner::preview(
            $profileid,
            $submitted->sourceedition,
            $submitted->targetedition,
            !empty($submitted->layoutconfirmed),
            $submitted->scopekey
        );
        redirect(new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $plan->id]));
    }
    if ($form && !$form->is_submitted()) {
        $form->set_data(['sourceedition' => optional_param('sourceedition', '', PARAM_TEXT),
            'targetedition' => optional_param('targetedition', '', PARAM_TEXT),
            'scopekey' => optional_param('scopekey', array_key_first($dimensions), PARAM_ALPHANUMEXT)]);
    }
}
foreach (
    $DB->get_records_sql(
        'SELECT p.id, p.timecreated, p.state, f.name FROM {local_multipicker_plan} p
       JOIN {local_multipicker} f ON f.id = p.profileid ORDER BY p.id DESC',
        [],
        0,
        10
    ) as $plan
) {
    $data['recent'][] = ['name' => \local_multipicker\output\display::name($plan->name), 'date' => userdate($plan->timecreated),
        'state' => display::outcome($plan->state),
        'url' => (new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $plan->id]))->out(false)];
}
$data['hasrecent'] = (bool)$data['recent'];
$data['chooseprofile'] = $step === 'profile';
$data['choosescope'] = $step === 'scope';
$data['profileurl'] = (new moodle_url('/local/multipicker/admin/migration.php', ['profileid' => $profileid]))->out(false);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_multipicker'));
$renderer = $PAGE->get_renderer('local_multipicker');
echo $renderer->render(navigation::site('migration'));
echo $renderer->render(new view('migration_setup', $data));
if ($form !== null) {
    echo html_writer::start_div('multipicker-admin multipicker-panel');
    $form->display();
    echo html_writer::end_div();
}
echo $OUTPUT->footer();
