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
 * Bulk migration configuration and saved previews.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_multipicker\output\display;
use local_multipicker\output\navigation;
use local_multipicker\output\view;

admin_externalpage_setup('local_multipicker_migration');
$planid = optional_param('planid', 0, PARAM_INT);
if ($planid) {
    redirect(new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $planid]));
}
if (
    !optional_param('new', false, PARAM_BOOL) && !optional_param('profileid', 0, PARAM_INT)
    && !optional_param('step', '', PARAM_ALPHA)
) {
    require(__DIR__ . '/migration_list.php');
    exit;
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
$form = null;
$data = ['profiles' => [], 'hasprofiles' => (bool)$profiles,
    'profilehelp' => $OUTPUT->help_icon('migrationprofile', 'local_multipicker'),
    'actionurl' => (new moodle_url('/local/multipicker/admin/migration.php'))->out(false)];
foreach ($profiles as $profile) {
    $data['profiles'][] = ['value' => $profile->id, 'label' => display::name($profile->name),
        'selected' => $profile->id == $profileid];
}
if ($profileid && $step === 'scope') {
    [$profile, $revision, $rules] = \local_multipicker\local\profile\profiles::load($profileid);
    $config = $rules->get_config();
    $dimensions = \local_multipicker\local\migration\course_scope::choices($rules);
    $data['hasscope'] = (bool)$dimensions;
    $form = new \local_multipicker\form\migration_form(null, [
        'profileid' => $profileid, 'dimensions' => $dimensions,
        'ordinal' => ($config['migration']['section_match']['strategy'] ?? 'ordinal') === 'ordinal',
        'migration' => $config['migration'] ?? []]);
    if ($form && ($submitted = $form->get_data())) {
        if ((int)$submitted->profileid !== $profileid) {
            throw new invalid_parameter_exception('The selected profile changed');
        }
        $plan = \local_multipicker\local\migration\migration_planner::preview(
            $profileid,
            implode(' / ', array_filter((array)($submitted->sourcefilters ?? []))),
            implode(' / ', array_filter((array)($submitted->targetfilters ?? []))),
            !empty($submitted->layoutconfirmed),
            '',
            ['migration' => \local_multipicker\local\migration\migration_rules::from_form((array)$submitted),
                'filters' => ['source' => (array)($submitted->sourcefilters ?? []),
                    'target' => (array)($submitted->targetfilters ?? [])]]
        );
        redirect(new moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $plan->id]));
    }
    if ($form && !$form->is_submitted()) {
        $defaults = ['sourcefilters' => [], 'targetfilters' => []];
        $previousplanid = optional_param('fromplan', 0, PARAM_INT);
        if ($previousplanid) {
            $previous = $DB->get_record(
                'local_multipicker_plan',
                ['id' => $previousplanid, 'profileid' => $profileid],
                '*',
                MUST_EXIST
            );
            $document = json_decode($previous->configjson, true);
            $defaults['sourcefilters'] = $document['filters']['source'] ?? [];
            $defaults['targetfilters'] = $document['filters']['target'] ?? [];
            $previousrules = $document['migration'] ?? [];
            if (!empty($previousrules['correspondences'])) {
                $rows = [];
                foreach ($previousrules['correspondences'] as $rule) {
                    $row = [];
                    foreach (['source', 'target'] as $side) {
                        $predicate = $rule[$side . '_module'];
                        $method = isset($predicate['pattern']) ? 'pattern'
                            : (isset($predicate['template']) ? 'template' : 'equals');
                        $row[$side . 'field'] = $predicate['field'];
                        $row[$side . 'method'] = $method;
                        $row[$side . 'value'] = $predicate[$method];
                    }
                    $rows[] = $row;
                }
                $defaults['correspondencesjson'] = json_encode($rows);
            }
            $sectionrows = [];
            foreach ($previousrules['section_match']['map'] ?? [] as $source => $target) {
                $sectionrows[] = ['source' => (string)$source, 'target' => (string)$target];
            }
            $defaults['sectionmapjson'] = json_encode($sectionrows);
            $defaults['sectionstrategy'] = $previousrules['section_match']['strategy'] ?? 'ordinal';
            $defaults['duplicatepolicy'] = $previousrules['same_content_different_path'] ?? 'skip-and-report';
            $defaults['missingtarget'] = $previousrules['missing_target'] ?? 'block';
            $defaults['conflictpolicy'] = $previousrules['same_path_different_content'] ?? 'block';
            $defaults['layoutconfirmed'] = $document['layoutconfirmed'] ?? false;
        }
        $form->set_data($defaults);
    }
}

$data['chooseprofile'] = $step === 'profile';
$data['choosescope'] = $step === 'scope';
$data['listurl'] = (new moodle_url('/local/multipicker/admin/migration.php'))->out(false);
$profileurl = new moodle_url('/local/multipicker/admin/migration.php', ['profileid' => $profileid, 'new' => 1]);
$data['profileurl'] = $profileurl->out(false);
$data['scopebackurl'] = (new moodle_url(
    '/local/multipicker/admin/migration.php',
    ['profileid' => $profileid, 'new' => 1, 'step' => 'profile']
))->out(false);
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
