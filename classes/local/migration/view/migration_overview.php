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
 * One migration, as its screen shows it.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration\view;

use local_filebank\local\migration\course_scope;
use local_filebank\local\migration\migration_rules;
use local_filebank\local\migration\migration_summary;
use local_filebank\local\migration\plan_history;
use local_filebank\local\profile\profile_config;
use local_filebank\local\profile\profiles;
use local_filebank\output\display;

/**
 * The header of a migration: where it goes, what it would do, what waits for a decision, and how its last run went.
 */
final class migration_overview {
    /**
     * What can be chosen for every file that has a given decision.
     */
    private const FILE_CHOICES = [
        'blocked_content' => ['skip', 'overwrite'],
        'blocked_attribution' => ['skip'],
        'blocked_policy' => ['skip'],
    ];

    /**
     * Describe a migration.
     *
     * @param int $planid The migration.
     * @return array The migration: identity, state, digest, counters, parameters, what waits for a decision and the last run.
     */
    public static function get(int $planid): array {
        global $DB;
        require_capability('local/filebank:planmigration', \context_system::instance());
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
        $document = json_decode($plan->configjson, true);
        $profile = $DB->get_record('local_filebank', ['id' => $plan->profileid], 'id, name');
        $run = $DB->get_record_sql(
            'SELECT * FROM {local_filebank_run} WHERE planid = :planid ORDER BY id DESC',
            ['planid' => $planid],
            IGNORE_MULTIPLE
        );
        $summary = migration_summary::stored($planid);
        [$rules, $outdated] = self::profile_rules($plan);
        $canexecute = has_capability('local/filebank:executemigration', \context_system::instance());
        $editable = !$run;
        $state = $run ? $run->state : $plan->state;
        $overview = [
            'id' => (int) $plan->id,
            'uuid' => $plan->uuid,
            'profileid' => (int) $plan->profileid,
            'profile' => $profile ? display::plain($profile->name) : '',
            'state' => $state,
            'statelabel' => display::outcome($state),
            'digest' => $plan->digest,
            'timecreated' => (int) $plan->timecreated,
            'datelabel' => userdate($plan->timecreated),
            'editable' => $editable,
            'outdated' => $outdated,
            'canexecute' => $canexecute && $editable && !$outdated && plan_history::has_work($planid),
            'summary' => self::summary($summary),
            'source' => migration_catalog::side($document, 'source'),
            'target' => migration_catalog::side($document, 'target'),
            'filters' => self::filters($rules, $document),
            'rules' => json_encode(migration_rules::to_ui($document['migration'] ?? [])),
            'natures' => self::natures($planid, $document),
        ];
        if ($run) {
            $overview['run'] = self::run($run, $summary, $canexecute);
        }
        return $overview;
    }

    /**
     * The counters, as the screens read them.
     *
     * @param array $summary What the summary class counted.
     * @return array The counters with the notes as a list.
     */
    public static function summary(array $summary): array {
        $notes = [];
        foreach ($summary['notes'] as $kind => $count) {
            $notes[] = ['kind' => $kind, 'count' => (int) $count,
                'label' => get_string('note_' . $kind, 'local_filebank', (int) $count)];
        }
        $summary['notes'] = $notes;
        return $summary;
    }

    /**
     * The rules of the profile of a migration, and whether the migration was worked out with an older revision of them.
     *
     * @param \stdClass $plan The migration.
     * @return array The rules, or null when the profile cannot be read any more, and whether the migration is outdated.
     */
    private static function profile_rules(\stdClass $plan): array {
        try {
            [, $revision, $rules] = profiles::load($plan->profileid);
        } catch (\moodle_exception | \dml_exception $exception) {
            return [null, true];
        }
        return [$rules, (int) $revision->id !== (int) $plan->revisionid];
    }

    /**
     * The filters that were asked for, with the name of the dimension.
     *
     * @param profile_config|null $rules Rules of the profile, or null when it cannot be read.
     * @param array $document Document of the migration.
     * @return array The source and the target filters as lists of key, label and value.
     */
    private static function filters(?profile_config $rules, array $document): array {
        $labels = $rules ? course_scope::choices($rules) : [];
        $filters = [];
        foreach (['source', 'target'] as $side) {
            $filters[$side] = [];
            foreach ($labels as $key => $label) {
                $filters[$side][] = ['key' => (string) $key, 'label' => strip_tags($label),
                    'value' => (string) ($document['filters'][$side][$key] ?? '')];
            }
        }
        return $filters;
    }

    /**
     * What waits for a decision, with what can be chosen for all of it at once.
     *
     * @param int $planid The migration.
     * @param array $document Document of the migration.
     * @return array The natures: kind, nature, label, count and the choices that apply to every one of them.
     */
    private static function natures(int $planid, array $document): array {
        global $DB;
        $natures = [];
        $rows = $DB->get_records_sql(
            'SELECT decision, COUNT(1) AS total
               FROM {local_filebank_operation}
              WHERE planid = :planid AND ' . $DB->sql_like('decision', ':blocked') . '
           GROUP BY decision
           ORDER BY decision',
            ['planid' => $planid, 'blocked' => 'blocked%']
        );
        foreach ($rows as $row) {
            $natures[] = ['kind' => 'file', 'nature' => $row->decision, 'count' => (int) $row->total,
                'label' => get_string_manager()->string_exists('nature_' . $row->decision, 'local_filebank')
                    ? get_string('nature_' . $row->decision, 'local_filebank') : $row->decision,
                'choices' => self::file_choices($row->decision)];
        }
        $reasons = [];
        foreach ($document['issues'] ?? [] as $issue) {
            $reasons[$issue['reason']] = ($reasons[$issue['reason']] ?? 0) + 1;
        }
        ksort($reasons);
        foreach ($reasons as $reason => $count) {
            $natures[] = ['kind' => 'issue', 'nature' => (string) $reason, 'count' => $count,
                'label' => display::reason((string) $reason), 'choices' => self::choices(['exclude'])];
        }
        return $natures;
    }

    /**
     * What can be chosen for a file that has a given decision.
     *
     * @param string $decision The decision of the file.
     * @return array The choices with their labels.
     */
    public static function file_choices(string $decision): array {
        return self::choices(self::FILE_CHOICES[$decision] ?? ['skip']);
    }

    /**
     * The choices that apply to a whole nature.
     *
     * @param string[] $values The choice codes.
     * @return array Each choice with its label.
     */
    private static function choices(array $values): array {
        return array_map(
            static fn(string $value): array => ['value' => $value, 'label' => get_string('choice_' . $value, 'local_filebank')],
            $values
        );
    }

    /**
     * How the last run is doing.
     *
     * @param \stdClass $run The run.
     * @param array $summary The counters of the migration.
     * @param bool $canexecute Whether the viewer may stop it.
     * @return array The run: state, progress and whether it can be stopped.
     */
    private static function run(\stdClass $run, array $summary, bool $canexecute): array {
        global $DB;
        $done = $DB->count_records_select(
            'local_filebank_operation',
            'planid = :planid AND included = 1 AND state <> :pending',
            ['planid' => $run->planid, 'pending' => 'pending']
        );
        $active = in_array($run->state, ['queued', 'running'], true);
        return ['id' => (int) $run->id, 'state' => $run->state, 'statelabel' => display::outcome($run->state),
            'done' => $done, 'total' => (int) $summary['included'], 'active' => $active,
            'cancancel' => $canexecute && $active];
    }
}
