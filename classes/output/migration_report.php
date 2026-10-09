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
 * Paged migration review presentation.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\output;

/**
 * Groups a bounded result page while retaining whole-plan counts and permissions.
 */
final class migration_report {
    /**
     * Export an immutable stored plan without loading every operation.
     *
     * @param int $planid Stored preview.
     * @param string $decision Decision group.
     * @param int $page Zero-based page.
     * @return array Template context.
     */
    public static function data(int $planid, string $decision = '', int $page = 0): array {
        global $CFG, $DB;
        require_capability('local/coursebridge:planmigration', \context_system::instance());
        if (!in_array($decision, ['', 'copy', 'skip', 'blocked'], true)) {
            throw new \invalid_parameter_exception('Unknown decision filter');
        }
        $plan = $DB->get_record('local_coursebridge_plan', ['id' => $planid], '*', MUST_EXIST);
        $document = json_decode($plan->configjson, true);
        $revision = $DB->get_record('local_coursebridge_revision', ['id' => $plan->revisionid], '*', MUST_EXIST);
        $config = json_decode($revision->configjson, true);
        $url = new \moodle_url('/local/coursebridge/admin/migration_review.php', ['planid' => $planid]);
        $data = ['planid' => $planid, 'uuid' => $plan->uuid, 'planstate' => display::outcome($plan->state),
            'sourceedition' => $document['sourceedition'], 'targetedition' => $document['targetedition'],
            'summary' => profile_summary::data($config) + ['revision' => $revision->revision],
            'actionurl' => $url->out(false), 'sesskey' => sesskey(), 'digest' => $plan->digest,
            'issues' => [], 'runs' => [], 'groups' => [], 'filters' => [],
            'newplanurl' => (new \moodle_url(
                '/local/coursebridge/admin/migration.php',
                ['profileid' => $plan->profileid, 'sourceedition' => $document['sourceedition'],
                'targetedition' => $document['targetedition']]
            ))->out(false),
            'maintenance' => !empty($CFG->maintenance_enabled), 'blocked' => $plan->state === 'blocked',
            'refreshurl' => $url->out(false),
        ];
        $counts = ['copy' => 0, 'skip' => 0, 'blocked' => 0];
        $total = 0;
        $completed = 0;
        $totals = $DB->get_recordset_sql(
            'SELECT decision, state, COUNT(1) AS amount FROM {local_coursebridge_operation}
              WHERE planid = ? GROUP BY decision, state',
            [$planid]
        );
        foreach ($totals as $row) {
            $counts[$row->decision === 'copy' ? 'copy' : (strpos($row->decision, 'skip_') === 0 ? 'skip' : 'blocked')]
                += (int)$row->amount;
            $total += (int)$row->amount;
            $completed += $row->state !== 'pending' ? (int)$row->amount : 0;
        }
        $totals->close();
        foreach ($counts as $key => $count) {
            $data['metrics'][] = ['count' => $count, 'label' => get_string('metric_' . $key, 'local_coursebridge')];
        }
        foreach (['' => $total] + $counts as $key => $count) {
            $data['filters'][] = ['label' => get_string($key === '' ? 'allfiles' : 'metric_' . $key, 'local_coursebridge'),
                'count' => $count, 'active' => $decision === $key,
                'url' => (new \moodle_url($url, ['decision' => $key]))->out(false)];
        }
        $condition = 'planid = :planid';
        $params = ['planid' => $planid];
        if ($decision === 'copy') {
            $condition .= ' AND decision = :decision';
            $params['decision'] = 'copy';
        } else if ($decision !== '') {
            $condition .= ' AND ' . $DB->sql_like('decision', ':decision');
            $params['decision'] = $DB->sql_like_escape($decision . '_') . '%';
        }
        $filtered = $decision === '' ? $total : $counts[$decision];
        $page = max(0, min($page, max(0, (int)ceil($filtered / 50) - 1)));
        $courses = [];
        $groups = [];
        foreach (
            $DB->get_records_select(
                'local_coursebridge_operation',
                $condition,
                $params,
                'targetcmid, id',
                '*',
                $page * 50,
                50
            ) as $operation
        ) {
            $snapshot = json_decode($operation->snapshot, true);
            $target = $snapshot['target'];
            foreach ([$snapshot['sourcecourse'], $target['courseid']] as $courseid) {
                $courses[$courseid] = $courses[$courseid] ?? ($DB->get_field('course', 'shortname', ['id' => $courseid])
                    ?: get_string('unknowncourse', 'local_coursebridge'));
            }
            $key = $snapshot['sourcecourse'] . ':' . $operation->targetcmid;
            if (!isset($groups[$key])) {
                $groups[$key] = ['sourcecourse' => $courses[$snapshot['sourcecourse']],
                    'targetcourse' => $courses[$target['courseid']], 'targetfolder' => $target['name'],
                    'targetsection' => $target['sectionname'] ?: get_string(
                        'sectionnumber',
                        'local_coursebridge',
                        $target['sectionnum']
                    ), 'operations' => []];
            }
            $result = json_decode($operation->result, true);
            $groups[$key]['operations'][] = ['filename' => $snapshot['source']['filename'],
                'filepath' => $snapshot['source']['filepath'],
                'author' => $snapshot['source']['author'] ?: get_string('unknownauthor', 'local_coursebridge'),
                'license' => $snapshot['source']['license'], 'decisionlabel' => display::outcome($operation->decision),
                'statelabel' => display::outcome($operation->state),
                'resultlabel' => isset($result['error']) ? display::reason($result['error'])
                    : (!empty($result['outcome']) ? display::outcome($result['outcome']) : '')];
        }
        $data['groups'] = array_values($groups);
        foreach ($document['issues'] as $issue) {
            $references = [];
            $courseids = array_merge(
                $issue['courses']['source'] ?? [],
                $issue['courses']['target'] ?? [],
                !empty($issue['targetcourseid']) ? [$issue['targetcourseid']] : []
            );
            if (!empty($issue['sourcecmid'])) {
                $cm = get_coursemodule_from_id(null, $issue['sourcecmid']);
                if ($cm) {
                    $courseids[] = $cm->course;
                    $references[] = $cm->name;
                }
            }
            foreach (array_unique($courseids) as $courseid) {
                $courses[$courseid] = $courses[$courseid] ?? ($DB->get_field('course', 'shortname', ['id' => $courseid])
                    ?: get_string('unknowncourse', 'local_coursebridge'));
                $references[] = $courses[$courseid];
            }
            $data['issues'][] = ['description' => display::reason($issue['reason']), 'context' => implode(' / ', $references)];
        }
        $canexecute = has_capability('local/coursebridge:executemigration', \context_system::instance());
        $data['canexecute'] = $plan->state === 'reviewable' && $canexecute && $total > $completed;
        foreach ($DB->get_records('local_coursebridge_run', ['planid' => $planid], 'id DESC') as $run) {
            $active = in_array($run->state, ['queued', 'running'], true);
            $data['runs'][] = ['id' => $run->id, 'statelabel' => display::outcome($run->state),
                'cancancel' => $canexecute && $active];
            if ($active) {
                $data['canexecute'] = false;
            }
        }
        $data['hasissues'] = (bool)$data['issues'];
        $data['hasruns'] = (bool)$data['runs'];
        $data['hasoperations'] = (bool)$data['groups'];
        $data['plancompleted'] = $completed;
        $data['plantotal'] = max(1, $total);
        $data['planprogress'] = get_string(
            'progresslabel',
            'local_coursebridge',
            (object)['done' => $completed, 'total' => $total]
        );
        $data['pageinfo'] = get_string(
            'reviewpage',
            'local_coursebridge',
            (object)['page' => $page + 1, 'pages' => max(1, (int)ceil($filtered / 50)), 'total' => $filtered]
        );
        foreach (['previousurl' => $page - 1, 'nexturl' => $page + 1] as $name => $number) {
            if ($number >= 0 && $number * 50 < $filtered) {
                $data[$name] = (new \moodle_url($url, ['decision' => $decision, 'page' => $number]))->out(false);
            }
        }
        return $data;
    }
}
