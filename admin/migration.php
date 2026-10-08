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
 * Readable reviewed migration decisions and progress.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_coursebridge\output\display;
use local_coursebridge\output\view;

admin_externalpage_setup('local_coursebridge_migration');
$planid = optional_param('planid', 0, PARAM_INT);
$form = new \local_coursebridge\form\migration_form();
if ($data = $form->get_data()) {
    $plan = \local_coursebridge\local\migration\migration_planner::preview(
        $data->profileid,
        $data->sourceedition,
        $data->targetedition,
        !empty($data->layoutconfirmed)
    );
    redirect(new moodle_url('/local/coursebridge/admin/migration.php', ['planid' => $plan->id]));
}
if (data_submitted() && optional_param('action', '', PARAM_ALPHA) !== '') {
    require_sesskey();
    $action = required_param('action', PARAM_ALPHA);
    if ($action === 'execute') {
        \local_coursebridge\local\migration\migration_executor::queue($planid, required_param('digest', PARAM_ALPHANUM));
    } else if ($action === 'cancel') {
        \local_coursebridge\local\migration\migration_executor::cancel(required_param('runid', PARAM_INT));
    } else {
        throw new invalid_parameter_exception('Unknown migration action');
    }
    redirect(new moodle_url('/local/coursebridge/admin/migration.php', ['planid' => $planid]));
}
echo $OUTPUT->header();
if (!$planid) {
    echo $OUTPUT->heading(get_string('preview', 'local_coursebridge'));
    echo html_writer::tag('p', get_string('migrationintro', 'local_coursebridge'), ['class' => 'text-muted']);
    $form->display();
} else {
    $plan = $DB->get_record('local_coursebridge_plan', ['id' => $planid], '*', MUST_EXIST);
    $document = json_decode($plan->configjson, true);
    $data = [
        'planid' => $planid, 'uuid' => $plan->uuid, 'planstate' => display::outcome($plan->state),
        'sourceedition' => $document['sourceedition'], 'targetedition' => $document['targetedition'],
        'actionurl' => (new moodle_url('/local/coursebridge/admin/migration.php'))->out(false),
        'newplanurl' => (new moodle_url('/local/coursebridge/admin/migration.php'))->out(false),
        'sesskey' => sesskey(), 'digest' => $plan->digest, 'operations' => [], 'issues' => [], 'runs' => [],
    ];
    $counts = ['copy' => 0, 'skip' => 0, 'blocked' => 0];
    $completed = 0;
    $runprocessed = [];
    $courses = [];
    foreach ($DB->get_records('local_coursebridge_operation', ['planid' => $planid], 'id') as $operation) {
        $snapshot = json_decode($operation->snapshot, true);
        $target = $snapshot['target'];
        $courses[$target['courseid']] = $courses[$target['courseid']] ?? get_course($target['courseid'])->shortname;
        $result = json_decode($operation->result, true);
        $data['operations'][] = [
            'filename' => $snapshot['source']['filename'], 'filepath' => $snapshot['source']['filepath'],
            'author' => $snapshot['source']['author'] ?: get_string('unknownauthor', 'local_coursebridge'),
            'license' => $snapshot['source']['license'], 'targetcourse' => $courses[$target['courseid']],
            'targetsection' => $target['sectionname'] ?: get_string('sectionnumber', 'local_coursebridge', $target['sectionnum']),
            'targetfolder' => $target['name'], 'decisionlabel' => display::outcome($operation->decision),
            'statelabel' => display::outcome($operation->state),
            'resultlabel' => isset($result['error']) ? display::reason($result['error'])
                : (!empty($result['outcome']) ? display::outcome($result['outcome']) : ''),
        ];
        $counts[$operation->decision === 'copy' ? 'copy'
            : (strpos($operation->decision, 'skip_') === 0 ? 'skip' : 'blocked')]++;
        $completed += $operation->state !== 'pending' ? 1 : 0;
        if (!empty($result['runid'])) {
            $runprocessed[$result['runid']] = ($runprocessed[$result['runid']] ?? 0) + 1;
        }
    }
    foreach ($document['issues'] as $issue) {
        $data['issues'][] = ['description' => display::reason($issue['reason'])];
    }
    foreach ($counts as $key => $count) {
        $data['metrics'][] = ['count' => $count, 'label' => get_string('metric_' . $key, 'local_coursebridge')];
    }
    $canexecute = has_capability('local/coursebridge:executemigration', context_system::instance());
    $data['canexecute'] = $plan->state === 'reviewable' && $canexecute && count($data['operations']) > $completed;
    $total = count($data['operations']);
    foreach ($DB->get_records('local_coursebridge_run', ['planid' => $planid], 'id DESC') as $run) {
        $processed = $runprocessed[$run->id] ?? 0;
        $data['runs'][] = [
            'id' => $run->id, 'statelabel' => display::outcome($run->state),
            'progresslabel' => get_string('runprocessed', 'local_coursebridge', $processed),
            'cancancel' => $canexecute && in_array($run->state, ['queued', 'running'], true),
        ];
        if (in_array($run->state, ['queued', 'running'], true)) {
            $data['canexecute'] = false;
        }
    }
    $data['hasissues'] = (bool)$data['issues'];
    $data['hasoperations'] = (bool)$data['operations'];
    $data['hasruns'] = (bool)$data['runs'];
    $data['plancompleted'] = $completed;
    $data['plantotal'] = max(1, $total);
    $data['planprogress'] = get_string('progresslabel', 'local_coursebridge', (object)['done' => $completed, 'total' => $total]);
    echo $PAGE->get_renderer('local_coursebridge')->render(new view('migration_report', $data));
}
echo $OUTPUT->footer();
