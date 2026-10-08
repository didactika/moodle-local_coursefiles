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
 * Revalidated idempotent migration execution.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local\migration;

use local_coursebridge\local\catalog\material_source;
use local_coursebridge\local\profile\profiles;
use local_coursebridge\local\policy\author_policy;
use local_coursebridge\local\logging\log;

/**
 * Executes immutable plans without silently repairing stale destinations.
 */
final class migration_executor {
    /**
     * Queue an accountable run after reviewing its digest.
     *
     * @param int $planid Plan.
     * @param string $digest Reviewed digest.
     * @return int Run.
     */
    public static function queue(int $planid, string $digest): int {
        global $CFG, $DB, $USER;
        require_capability('local/coursebridge:executemigration', \context_system::instance());
        $plan = $DB->get_record('local_coursebridge_plan', ['id' => $planid], '*', MUST_EXIST);
        if (
            $plan->state !== 'reviewable' || !hash_equals($plan->digest, $digest)
                || !hash_equals($plan->digest, hash('sha256', $plan->configjson))
        ) {
            throw new \moodle_exception('planstale', 'local_coursebridge');
        }
        if (empty($CFG->maintenance_enabled)) {
            throw new \moodle_exception('maintenance_required', 'local_coursebridge');
        }
        $runid = $DB->insert_record('local_coursebridge_run', (object)[
            'planid' => $planid, 'userid' => $USER->id, 'state' => 'queued', 'cancelled' => 0,
            'timecreated' => time(), 'timemodified' => time(),
        ]);
        $task = new \local_coursebridge\task\execute_migration();
        $task->set_userid($USER->id);
        $task->set_custom_data(['runid' => $runid]);
        \core\task\manager::queue_adhoc_task($task);
        log::record('migration_queued', $plan->uuid, ['runid' => $runid]);
        return $runid;
    }

    /**
     * Execute a bounded slice.
     *
     * @param int $runid Run.
     * @return bool More eligible operations remain.
     */
    public static function execute(int $runid): bool {
        global $CFG, $DB, $USER;
        $run = $DB->get_record('local_coursebridge_run', ['id' => $runid], '*', MUST_EXIST);
        $plan = $DB->get_record('local_coursebridge_plan', ['id' => $run->planid], '*', MUST_EXIST);
        if ((int)$USER->id !== (int)$run->userid) {
            throw new \moodle_exception('planstale', 'local_coursebridge');
        }
        require_capability('local/coursebridge:executemigration', \context_system::instance());
        if (empty($CFG->maintenance_enabled)) {
            throw new \moodle_exception('maintenance_required', 'local_coursebridge');
        }
        if ($run->cancelled) {
            $DB->set_field('local_coursebridge_run', 'state', 'cancelled', ['id' => $runid]);
            return false;
        }
        if (!hash_equals($plan->digest, hash('sha256', $plan->configjson))) {
            throw new \moodle_exception('planstale', 'local_coursebridge');
        }
        $DB->set_field('local_coursebridge_run', 'state', 'running', ['id' => $runid]);
        [$profile, $revision, $rules] = profiles::load($plan->profileid);
        if ((int)$revision->id !== (int)$plan->revisionid) {
            $DB->set_field('local_coursebridge_run', 'state', 'blocked', ['id' => $runid]);
            log::record('migration_blocked', $plan->uuid, ['runid' => $runid, 'reason' => 'planstale']);
            return false;
        }
        $operations = $DB->get_records(
            'local_coursebridge_operation',
            ['planid' => $plan->id, 'state' => 'pending'],
            'id',
            '*',
            0,
            25
        );
        foreach ($operations as $operation) {
            if ($DB->get_field('local_coursebridge_run', 'cancelled', ['id' => $runid])) {
                break;
            }
            $lock = \core\lock\lock_config::get_lock_factory('local_coursebridge')
                ->get_lock('destination:' . $operation->targetcmid, 10);
            if (!$lock) {
                throw new \moodle_exception('lockfailed', 'local_coursebridge');
            }
            try {
                $operation = $DB->get_record('local_coursebridge_operation', ['id' => $operation->id], '*', MUST_EXIST);
                if ($operation->state !== 'pending') {
                    continue;
                }
                $snapshot = json_decode($operation->snapshot, true);
                $target = placement::snapshot($operation->targetcmid);
                require_capability('moodle/course:manageactivities', \context_module::instance($operation->targetcmid));
                [$source, $sourcecm] = material_source::resolve(
                    $operation->sourcehash,
                    $target['courseid'],
                    $plan->profileid
                );
                if (
                    $target !== $snapshot['target']
                        || copy_decision::snapshot($source) !== $snapshot['source']
                        || $rules->resolve(get_course($sourcecm->course)) !== $snapshot['sourceidentity']
                        || $rules->resolve(get_course($target['courseid'])) !== $snapshot['targetidentity']
                ) {
                    throw new \moodle_exception('planstale', 'local_coursebridge');
                }
                $context = \context_module::instance($operation->targetcmid);
                $existing = get_file_storage()->get_file(
                    $context->id,
                    'mod_folder',
                    'content',
                    0,
                    $source->get_filepath(),
                    $source->get_filename()
                );
                $actual = $existing ? copy_decision::snapshot($existing) : null;
                $decision = copy_decision::classify($snapshot['source'], $actual);
                if ($operation->decision === 'skip_content') {
                    $duplicate = get_file_storage()->get_file_by_hash($snapshot['duplicate']['pathnamehash']);
                    if (!$duplicate || copy_decision::snapshot($duplicate) !== $snapshot['duplicate']) {
                        throw new \moodle_exception('planstale', 'local_coursebridge');
                    }
                    $decision = 'skip_content';
                }
                if ($actual !== $snapshot['existing'] && $decision !== 'skip_identical') {
                    throw new \moodle_exception('planstale', 'local_coursebridge');
                }
                if ($decision === 'copy') {
                    author_policy::require_native_copy($source, $sourcecm->course, $target['courseid']);
                    $attributionid = author_policy::capture($source);
                    $saved = folder_destination::copy($source, $operation->targetcmid);
                    $DB->insert_record('local_coursebridge_origin', (object)[
                        'pathnamehash' => $saved->get_pathnamehash(), 'attributionid' => $attributionid,
                        'contenthash' => $saved->get_contenthash(), 'userid' => $USER->id, 'timecreated' => time(),
                    ]);
                    $outcome = ['runid' => $runid, 'outcome' => 'copied', 'pathnamehash' => $saved->get_pathnamehash()];
                } else if ($decision === 'skip_identical' || $decision === 'skip_content') {
                    $outcome = ['runid' => $runid, 'outcome' => 'already_present'];
                } else {
                    throw new \moodle_exception('planstale', 'local_coursebridge');
                }
                $operation->state = 'done';
                $operation->result = json_encode($outcome);
            } catch (\moodle_exception $exception) {
                $operation->state = 'blocked';
                $operation->result = json_encode(['runid' => $runid, 'error' => $exception->errorcode]);
            } finally {
                $operation->timemodified = time();
                $DB->update_record('local_coursebridge_operation', $operation);
                $lock->release();
            }
        }
        $more = $DB->record_exists('local_coursebridge_operation', ['planid' => $plan->id, 'state' => 'pending']);
        $cancelled = $DB->get_field('local_coursebridge_run', 'cancelled', ['id' => $runid]);
        $state = $cancelled ? 'cancelled' : ($more ? 'running' : (
            $DB->record_exists('local_coursebridge_operation', ['planid' => $plan->id, 'state' => 'blocked'])
                ? 'partial' : 'completed'));
        $DB->update_record('local_coursebridge_run', (object)[
            'id' => $runid, 'state' => $state, 'timemodified' => time(),
        ]);
        log::record('migration_batch', $plan->uuid, ['runid' => $runid, 'state' => $state]);
        return $more && !$cancelled;
    }

    /**
     * Request cancellation at the next operation boundary.
     *
     * @param int $runid Run.
     */
    public static function cancel(int $runid): void {
        global $DB;
        require_capability('local/coursebridge:executemigration', \context_system::instance());
        $DB->set_field('local_coursebridge_run', 'cancelled', 1, ['id' => $runid]);
    }
}
