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
 * Paged migration review presentation.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\output;

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
     * @param string $search Course, folder or filename search.
     * @param bool $execution Display the execution step.
     * @param int $issuepage Independent zero-based course/placement issue page.
     * @param int $perpage Bounded page size.
     * @return array Template context.
     */
    public static function data(
        int $planid,
        string $decision = '',
        int $page = 0,
        string $search = '',
        bool $execution = false,
        int $issuepage = 0,
        int $perpage = 50
    ): array {
        global $DB, $OUTPUT;
        require_capability('local/multipicker:planmigration', \context_system::instance());
        if (!in_array($decision, ['', 'copy', 'skip', 'blocked'], true)) {
            throw new \invalid_parameter_exception('Unknown decision filter');
        }
        $plan = $DB->get_record('local_multipicker_plan', ['id' => $planid], '*', MUST_EXIST);
        $document = json_decode($plan->configjson, true);
        $revision = $DB->get_record('local_multipicker_revision', ['id' => $plan->revisionid], '*', MUST_EXIST);
        $config = json_decode($revision->configjson, true);
        $config['migration'] = $document['migration'] ?? $config['migration'] ?? [];
        $perpage = in_array($perpage, [10, 25, 50], true) ? $perpage : 50;
        $url = new \moodle_url('/local/multipicker/admin/migration_review.php', ['planid' => $planid, 'perpage' => $perpage]);
        $data = ['planid' => $planid, 'uuid' => $plan->uuid, 'planstate' => display::outcome($plan->state),
            'sourceedition' => $document['sourceedition'], 'targetedition' => $document['targetedition'],
            'executionstep' => $execution, 'reviewstep' => !$execution, 'search' => $search,
            'selectioneditable' => !$DB->record_exists('local_multipicker_run', ['planid' => $planid]),
            'reviewurl' => $url->out(false), 'executionurl' => (new \moodle_url($url, ['step' => 'execute']))->out(false),
            'summary' => profile_summary::data($config) + ['revision' => $revision->revision],
            'actionurl' => $url->out(false), 'sesskey' => sesskey(), 'digest' => $plan->digest, 'perpage' => $perpage,
            'contextid' => \context_system::instance()->id,
            'selectionhelp' => $OUTPUT->help_icon('reviewselection', 'local_multipicker'),
            'searchhelp' => $OUTPUT->help_icon('migrationsearch', 'local_multipicker'),
            'executionhelp' => $OUTPUT->help_icon('confirmmigration', 'local_multipicker'),
            'issues' => [], 'runs' => [], 'groups' => [], 'filters' => [],
            'newplanurl' => (new \moodle_url(
                '/local/multipicker/admin/migration.php',
                ['fromplan' => $plan->id, 'profileid' => $plan->profileid, 'sourceedition' => $document['sourceedition'],
                'targetedition' => $document['targetedition'], 'scopekey' => $document['scopekey'] ?? 'edition', 'step' => 'scope']
            ))->out(false),
            'blocked' => $plan->state === 'blocked',
            'emptycancelled' => $plan->state === 'cancelled' && !\local_multipicker\local\migration\plan_history::has_work($planid),
            'refreshurl' => $url->out(false),
        ];
        $counts = ['copy' => 0, 'skip' => 0, 'blocked' => 0];
        $total = 0;
        $includedtotal = 0;
        $completed = 0;
        $totals = $DB->get_recordset_sql(
            'SELECT decision, state, included, COUNT(1) AS amount FROM {local_multipicker_operation}
              WHERE planid = ? GROUP BY decision, state, included',
            [$planid]
        );
        foreach ($totals as $row) {
            $category = in_array($row->decision, ['copy', 'overwrite'], true) ? 'copy'
                : (str_starts_with($row->decision, 'skip_') ? 'skip' : 'blocked');
            $counts[$category] += (int)$row->amount;
            $total += (int)$row->amount;
            $includedtotal += $row->included ? (int)$row->amount : 0;
            $completed += $row->included && $row->state !== 'pending' ? (int)$row->amount : 0;
        }
        $totals->close();
        $data['total'] = $total;
        foreach ($counts as $key => $count) {
            $data['metrics'][] = ['count' => $count, 'label' => get_string('metric_' . $key, 'local_multipicker')];
        }
        foreach (['' => $total] + $counts as $key => $count) {
            $data['filters'][] = ['label' => get_string($key === '' ? 'allfiles' : 'metric_' . $key, 'local_multipicker'),
                'count' => $count, 'active' => $decision === $key,
                'url' => (new \moodle_url($url, ['decision' => $key, 'q' => $search]))->out(false)];
        }
        $condition = 'planid = :planid';
        $params = ['planid' => $planid];
        if ($decision === 'copy') {
            $condition .= ' AND (decision = :copydecision OR decision = :overwritedecision)';
            $params['copydecision'] = 'copy';
            $params['overwritedecision'] = 'overwrite';
        } else if ($decision !== '') {
            $condition .= ' AND ' . $DB->sql_like('decision', ':decision');
            $params['decision'] = $DB->sql_like_escape($decision . '_') . '%';
        }
        if ($search !== '') {
            // Cross-database text search covers immutable file/folder snapshots and current course shortnames.
            $condition .= ' AND (' . $DB->sql_like('snapshot', ':snapshotsearch', false)
                . ' OR targetcmid IN (SELECT cm.id FROM {course_modules} cm JOIN {course} c ON c.id = cm.course WHERE '
                . $DB->sql_like('c.shortname', ':coursesearch', false) . '))';
            $params['snapshotsearch'] = '%' . $DB->sql_like_escape($search) . '%';
            $params['coursesearch'] = $params['snapshotsearch'];
        }
        $filtered = $DB->count_records_select('local_multipicker_operation', $condition, $params);
        $data['included'] = $DB->count_records('local_multipicker_operation', ['planid' => $planid, 'included' => 1]);
        $data['includedlabel'] = get_string('includedcount', 'local_multipicker', $data['included']);
        $data['editable'] = $data['selectioneditable'];
        $data['page'] = $page;
        $data['decision'] = $decision;
        $page = pagination::clamp($filtered, $page, $perpage);
        $data['page'] = $page;
        $courses = [];
        $groups = [];
        foreach (
            $DB->get_records_select(
                'local_multipicker_operation',
                $condition,
                $params,
                'targetcmid, id',
                '*',
                $page * $perpage,
                $perpage
            ) as $operation
        ) {
            $snapshot = json_decode($operation->snapshot, true);
            $target = $snapshot['target'];
            foreach ([$snapshot['sourcecourse'], $target['courseid']] as $courseid) {
                $courses[$courseid] = $courses[$courseid] ?? ($DB->get_field('course', 'shortname', ['id' => $courseid])
                    ?: get_string('unknowncourse', 'local_multipicker'));
            }
            $targetkey = $operation->targetcmid ?: ($snapshot['targetkey'] ?? '');
            $key = $snapshot['sourcecourse'] . ':' . $operation->sourcecmid . ':' . $targetkey;
            if (!isset($groups[$key])) {
                $groups[$key] = ['sourcecourse' => $courses[$snapshot['sourcecourse']],
                    'targetcourse' => $courses[$target['courseid']], 'targetfolder' => $target['name'],
                    'targetsection' => $target['sectionname'] ?: get_string(
                        'sectionnumber',
                        'local_multipicker',
                        $target['sectionnum']
                    ), 'operations' => [], 'groupcourseid' => $operation->sourcecourseid, 'groupcmid' => $operation->sourcecmid];
                $sourcecontext = \context_course::instance($snapshot['sourcecourse'], IGNORE_MISSING)
                    ?: \context_system::instance();
                $targetcontext = \context_course::instance($target['courseid'], IGNORE_MISSING) ?: \context_system::instance();
                $foldercontext = \context_module::instance($operation->targetcmid, IGNORE_MISSING) ?: $targetcontext;
                $groups[$key] += [
                    'sourcecoursehtml' => display::name($courses[$snapshot['sourcecourse']], $sourcecontext),
                    'targetcoursehtml' => display::name($courses[$target['courseid']], $targetcontext),
                    'targetfolderhtml' => display::name($target['name'], $foldercontext),
                    'sourcefolderhtml' => display::name($snapshot['sourcefoldername'] ?? '', $sourcecontext),
                    'targetsectionhtml' => display::name($groups[$key]['targetsection'], $targetcontext),
                ];
            }
            $result = json_decode($operation->result, true);
            $groups[$key]['operations'][] = ['id' => $operation->id, 'included' => (bool)$operation->included,
                'resolvable' => $data['selectioneditable'] && $operation->decision === 'blocked_content',
                'resolutionhelp' => $OUTPUT->help_icon('conflictpolicy', 'local_multipicker'),
                'filename' => $snapshot['source']['filename'],
                'sourcecourseid' => $operation->sourcecourseid, 'sourcecmid' => $operation->sourcecmid,
                'filepath' => $snapshot['source']['filepath'],
                'author' => $snapshot['source']['author'] ?: get_string('unknownauthor', 'local_multipicker'),
                'license' => $snapshot['source']['license'], 'decisionlabel' => display::outcome($operation->decision),
                'statelabel' => display::outcome($operation->state),
                'resultlabel' => isset($result['error']) ? display::reason($result['error'])
                    : (!empty($result['outcome']) ? display::outcome($result['outcome']) : '')];
        }
        $data['groups'] = array_values($groups);
        $coursegroups = [];
        foreach ($data['groups'] as &$group) {
            $conditions = ['planid' => $planid, 'sourcecourseid' => $group['groupcourseid'],
                'sourcecmid' => $group['groupcmid']];
            $grouptotal = $DB->count_records('local_multipicker_operation', $conditions);
            $groupincluded = $DB->count_records('local_multipicker_operation', $conditions + ['included' => 1]);
            $group['grouptotal'] = $grouptotal;
            $group['groupincluded'] = $groupincluded;
            $group['groupchecked'] = $grouptotal > 0 && $groupincluded === $grouptotal;
            $group['grouppartial'] = $groupincluded > 0 && !$group['groupchecked'];
            $key = $group['groupcourseid'] . ':' . $group['targetcourse'];
            if (!isset($coursegroups[$key])) {
                $coursegroups[$key] = $group;
                $coursegroups[$key]['folders'] = [];
                $conditions = ['planid' => $planid, 'sourcecourseid' => $group['groupcourseid']];
                $total = $DB->count_records('local_multipicker_operation', $conditions);
                $included = $DB->count_records('local_multipicker_operation', $conditions + ['included' => 1]);
                $coursegroups[$key]['coursetotal'] = $total;
                $coursegroups[$key]['courseincluded'] = $included;
                $coursegroups[$key]['coursechecked'] = $total > 0 && $total === $included;
                $coursegroups[$key]['coursepartial'] = $included > 0 && $included < $total;
            }
            $coursegroups[$key]['folders'][] = $group;
        }
        unset($group);
        $data['courses'] = array_values($coursegroups);
        $issuecount = count($document['issues']);
        $issuepage = pagination::clamp($issuecount, $issuepage, $perpage);
        $data['issuepage'] = $issuepage;
        $data['issuepageinfo'] = get_string('reviewissuepage', 'local_multipicker', (object)[
            'page' => $issuepage + 1, 'pages' => max(1, (int)ceil($issuecount / $perpage)), 'total' => $issuecount,
        ]);
        $destinations = [];
        foreach (array_slice($document['issues'], $issuepage * $perpage, $perpage, true) as $index => $issue) {
            $references = [];
            $courseids = array_merge(
                $issue['courses']['source'] ?? [],
                $issue['courses']['target'] ?? [],
                !empty($issue['targetcourseid']) ? [$issue['targetcourseid']] : [],
                !empty($issue['sourcecourseid']) ? [$issue['sourcecourseid']] : []
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
                    ?: get_string('unknowncourse', 'local_multipicker'));
                $references[] = $courses[$courseid];
            }
            $choices = [];
            if ($data['selectioneditable'] && ($issue['blocking'] ?? true)) {
                $choices[] = ['value' => 'exclude', 'label' => get_string(
                    !empty($issue['sourcecmid']) ? 'exclude_source' : 'exclude_pair',
                    'local_multipicker'
                )];
                if (
                    !empty($issue['targetcourseid']) && !empty($issue['sourcecmid'])
                    && in_array($issue['reason'], ['placementambiguous', 'creationnamerequired'], true)
                ) {
                    $courseid = (int)$issue['targetcourseid'];
                    require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
                    if (!isset($destinations[$courseid])) {
                        $destinations[$courseid] = [];
                        foreach (get_fast_modinfo($courseid)->get_cms() as $destination) {
                            if ($destination->modname === 'folder') {
                                $destinations[$courseid][] = ['value' => 'folder:' . $destination->id,
                                    'label' => get_string(
                                        'use_destination',
                                        'local_multipicker',
                                        strip_tags(display::name($destination->name, $destination->context))
                                    )];
                            }
                        }
                        if (has_capability('mod/folder:addinstance', \context_course::instance($courseid))) {
                            foreach (get_fast_modinfo($courseid)->get_section_info_all() as $section) {
                                $label = get_section_name(get_course($courseid), $section);
                                $destinations[$courseid][] = ['value' => 'create:' . $section->id,
                                    'label' => get_string('create_destination', 'local_multipicker', strip_tags($label))];
                            }
                        }
                    }
                    $choices = array_merge($choices, $destinations[$courseid]);
                }
            }
            $data['issues'][] = ['id' => $index, 'description' => display::reason($issue['reason']),
                'context' => implode(' / ', $references), 'choices' => $choices, 'resolvable' => (bool)$choices,
                'help' => $OUTPUT->help_icon('resolvemigration', 'local_multipicker')];
        }
        $canexecute = has_capability('local/multipicker:executemigration', \context_system::instance());
        $data['canexecute'] = $plan->state === 'reviewable' && $canexecute
            && $DB->record_exists('local_multipicker_operation', ['planid' => $planid, 'included' => 1, 'state' => 'pending']);
        foreach ($DB->get_records('local_multipicker_run', ['planid' => $planid], 'id DESC') as $run) {
            $active = in_array($run->state, ['queued', 'running'], true);
            $data['runs'][] = ['id' => $run->id, 'statelabel' => display::outcome($run->state),
                'cancancel' => $canexecute && $active];
            if ($active) {
                $data['canexecute'] = false;
            }
        }
        $data['hasissues'] = $issuecount > 0;
        foreach (['issuepreviousurl' => $issuepage - 1, 'issuenexturl' => $issuepage + 1] as $name => $number) {
            if ($number >= 0 && $number * $perpage < $issuecount) {
                $data[$name] = (new \moodle_url($url, [
                    'decision' => $decision, 'page' => $page, 'q' => $search, 'issuepage' => $number,
                    'step' => $execution ? 'execute' : '',
                ]))->out(false);
            }
        }
        $data['hasruns'] = (bool)$data['runs'];
        $data['hasoperations'] = (bool)$data['groups'];
        $data['plancompleted'] = $completed;
        $data['plantotal'] = max(1, $includedtotal);
        $data['planprogress'] = get_string(
            'progresslabel',
            'local_multipicker',
            (object)['done' => $completed, 'total' => $includedtotal]
        );
        $data['pageinfo'] = get_string(
            'reviewpage',
            'local_multipicker',
            (object)['page' => $page + 1, 'pages' => max(1, (int)ceil($filtered / $perpage)), 'total' => $filtered]
        );
        foreach (['previousurl' => $page - 1, 'nexturl' => $page + 1] as $name => $number) {
            if ($number >= 0 && $number * $perpage < $filtered) {
                $data[$name] = (new \moodle_url($url, [
                    'decision' => $decision, 'page' => $number, 'q' => $search, 'issuepage' => $issuepage,
                ]))->out(false);
            }
        }
        $data['allchecked'] = $data['total'] > 0 && $data['included'] === $data['total'];
        $data['allpartial'] = $data['included'] > 0 && $data['included'] < $data['total'];
        $data['pagination'] = pagination::render(
            $filtered,
            $page,
            $perpage,
            new \moodle_url($url, ['decision' => $decision, 'q' => $search, 'issuepage' => $issuepage])
        );
        $data['issuepagination'] = pagination::render(
            $issuecount,
            $issuepage,
            $perpage,
            new \moodle_url($url, ['decision' => $decision, 'q' => $search, 'page' => $page]),
            'issuepage'
        );
        return $data;
    }
}
