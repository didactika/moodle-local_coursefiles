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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

use local_multipicker\local\catalog\material_source;
use local_multipicker\local\profile\profiles;
use local_multipicker\local\policy\author_policy;
use local_multipicker\local\logging\log;

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
        require_capability('local/multipicker:executemigration', \context_system::instance());
        $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')->get_lock('plan:' . $planid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_multipicker');
        }
        $profilelock = null;
        try {
            $plan = $DB->get_record('local_multipicker_plan', ['id' => $planid], '*', MUST_EXIST);
            $profilelock = \core\lock\lock_config::get_lock_factory('local_multipicker')
                ->get_lock('profile:' . $plan->profileid, 10);
            if (!$profilelock) {
                throw new \moodle_exception('lockfailed', 'local_multipicker');
            }
            [, $revision] = profiles::load($plan->profileid);
            if ((int)$revision->id !== (int)$plan->revisionid) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            if (
                $plan->state !== 'reviewable' || !hash_equals($plan->digest, $digest)
                    || !hash_equals($plan->digest, hash('sha256', $plan->configjson))
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            if (empty($CFG->maintenance_enabled)) {
                throw new \moodle_exception('maintenance_required', 'local_multipicker');
            }
            $document = json_decode($plan->configjson, true);
            if (
                isset($document['operationsdigest'])
                    && !hash_equals($document['operationsdigest'], plan_selection::manifest_digest($planid))
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            if (
                isset($document['selectiondigest'])
                    && !hash_equals($document['selectiondigest'], plan_selection::digest($planid))
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            if (
                !$DB->record_exists('local_multipicker_operation', ['planid' => $planid, 'included' => 1, 'state' => 'pending'])
                    || $DB->record_exists_select(
                        'local_multipicker_run',
                        'planid = ? AND (state = ? OR state = ?)',
                        [$planid, 'queued', 'running']
                    )
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            $transaction = $DB->start_delegated_transaction();
            $runid = $DB->insert_record('local_multipicker_run', (object)[
                'planid' => $planid, 'userid' => $USER->id, 'state' => 'queued', 'cancelled' => 0,
                'timecreated' => time(), 'timemodified' => time(),
            ]);
            $task = new \local_multipicker\task\execute_migration();
            $task->set_userid($USER->id);
            $task->set_custom_data(['runid' => $runid]);
            \core\task\manager::queue_adhoc_task($task);
            log::record('migration_queued', $plan->uuid, ['runid' => $runid]);
            $transaction->allow_commit();
            return $runid;
        } finally {
            if ($profilelock) {
                $profilelock->release();
            }
            $lock->release();
        }
    }

    /**
     * Execute a bounded slice.
     *
     * @param int $runid Run.
     * @return bool More eligible operations remain.
     */
    public static function execute(int $runid): bool {
        global $CFG, $DB, $USER;
        $run = $DB->get_record('local_multipicker_run', ['id' => $runid], '*', MUST_EXIST);
        $plan = $DB->get_record('local_multipicker_plan', ['id' => $run->planid], '*', MUST_EXIST);
        if ((int)$USER->id !== (int)$run->userid) {
            throw new \moodle_exception('planstale', 'local_multipicker');
        }
        require_capability('local/multipicker:executemigration', \context_system::instance());
        if (empty($CFG->maintenance_enabled)) {
            throw new \moodle_exception('maintenance_required', 'local_multipicker');
        }
        if ($run->cancelled) {
            $DB->set_field('local_multipicker_run', 'state', 'cancelled', ['id' => $runid]);
            return false;
        }
        if (!hash_equals($plan->digest, hash('sha256', $plan->configjson))) {
            throw new \moodle_exception('planstale', 'local_multipicker');
        }
        $DB->set_field('local_multipicker_run', 'state', 'running', ['id' => $runid]);
        [$profile, $revision, $rules] = profiles::load($plan->profileid);
        if ((int)$revision->id !== (int)$plan->revisionid) {
            $DB->set_field('local_multipicker_run', 'state', 'blocked', ['id' => $runid]);
            log::record('migration_blocked', $plan->uuid, ['runid' => $runid, 'reason' => 'planstale']);
            return false;
        }
        $operations = $DB->get_records(
            'local_multipicker_operation',
            ['planid' => $plan->id, 'state' => 'pending', 'included' => 1],
            'id',
            '*',
            0,
            25
        );
        foreach ($operations as $operation) {
            if ($DB->get_field('local_multipicker_run', 'cancelled', ['id' => $runid])) {
                break;
            }
            $preview = json_decode($operation->snapshot, true);
            $lock = \core\lock\lock_config::get_lock_factory('local_multipicker')
                ->get_lock('destination:' . ($operation->targetcmid ?: 'create:' . $preview['targetkey']), 10);
            if (!$lock) {
                throw new \moodle_exception('lockfailed', 'local_multipicker');
            }
            $destinationlock = null;
            try {
                $operation = $DB->get_record('local_multipicker_operation', ['id' => $operation->id], '*', MUST_EXIST);
                if ($operation->state !== 'pending') {
                    continue;
                }
                $snapshot = json_decode($operation->snapshot, true);
                [$source, $sourcecm] = material_source::resolve(
                    $operation->sourcehash,
                    $snapshot['target']['courseid'],
                    $plan->profileid
                );
                if (
                    copy_decision::snapshot($source) !== $snapshot['source']
                    || $rules->resolve(get_course($sourcecm->course)) !== $snapshot['sourceidentity']
                    || $rules->resolve(get_course($snapshot['target']['courseid'])) !== $snapshot['targetidentity']
                ) {
                    throw new \moodle_exception('planstale', 'local_multipicker');
                }
                if (in_array($operation->decision, ['copy', 'overwrite'], true)) {
                    author_policy::require_native_copy($source, $sourcecm->course, $snapshot['target']['courseid']);
                }
                $target = $operation->targetcmid ? placement::snapshot($operation->targetcmid)
                    : target_binding::resolve($plan->id, $snapshot['targetkey'], $snapshot['target']);
                $targetcmid = $target['cmid'];
                if (!$operation->targetcmid) {
                    $destinationlock = \core\lock\lock_config::get_lock_factory('local_multipicker')
                        ->get_lock('destination:' . $targetcmid, 10);
                    if (!$destinationlock) {
                        throw new \moodle_exception('lockfailed', 'local_multipicker');
                    }
                    if (placement::snapshot($targetcmid) !== $target) {
                        throw new \moodle_exception('planstale', 'local_multipicker');
                    }
                }
                require_capability('moodle/course:manageactivities', \context_module::instance($targetcmid));
                [$source, $sourcecm] = material_source::resolve(
                    $operation->sourcehash,
                    $target['courseid'],
                    $plan->profileid
                );
                if (
                    ($operation->targetcmid && $target !== $snapshot['target'])
                        || copy_decision::snapshot($source) !== $snapshot['source']
                        || $rules->resolve(get_course($sourcecm->course)) !== $snapshot['sourceidentity']
                        || $rules->resolve(get_course($target['courseid'])) !== $snapshot['targetidentity']
                ) {
                    throw new \moodle_exception('planstale', 'local_multipicker');
                }
                $context = \context_module::instance($targetcmid);
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
                        throw new \moodle_exception('planstale', 'local_multipicker');
                    }
                    $decision = 'skip_content';
                }
                if ($actual !== $snapshot['existing'] && $decision !== 'skip_identical') {
                    throw new \moodle_exception('planstale', 'local_multipicker');
                }
                if ($operation->decision === 'overwrite' && $decision === 'blocked_content') {
                    $decision = 'overwrite';
                }
                if ($decision === 'copy' || $decision === 'overwrite') {
                    author_policy::require_native_copy($source, $sourcecm->course, $target['courseid']);
                    $attributionid = author_policy::capture($source);
                    $saved = folder_destination::copy($source, $targetcmid, $decision === 'overwrite');
                    $origin = (object)[
                        'pathnamehash' => $saved->get_pathnamehash(), 'attributionid' => $attributionid,
                        'contenthash' => $saved->get_contenthash(), 'userid' => $USER->id, 'timecreated' => time(),
                    ];
                    $previous = $DB->get_field('local_multipicker_origin', 'id', ['pathnamehash' => $origin->pathnamehash]);
                    if ($previous) {
                        $origin->id = $previous;
                        $DB->update_record('local_multipicker_origin', $origin);
                    } else {
                        $DB->insert_record('local_multipicker_origin', $origin);
                    }
                    $outcome = ['runid' => $runid, 'outcome' => 'copied', 'pathnamehash' => $saved->get_pathnamehash()];
                } else if ($decision === 'skip_identical' || $decision === 'skip_content') {
                    $outcome = ['runid' => $runid, 'outcome' => 'already_present'];
                } else {
                    throw new \moodle_exception('planstale', 'local_multipicker');
                }
                $operation->state = 'done';
                $operation->result = json_encode($outcome);
            } catch (\moodle_exception $exception) {
                $operation->state = 'blocked';
                $operation->result = json_encode(['runid' => $runid, 'error' => $exception->errorcode]);
            } finally {
                $operation->timemodified = time();
                $DB->update_record('local_multipicker_operation', $operation);
                if ($destinationlock) {
                    $destinationlock->release();
                }
                $lock->release();
            }
        }
        $more = $DB->record_exists('local_multipicker_operation', ['planid' => $plan->id, 'state' => 'pending', 'included' => 1]);
        $cancelled = $DB->get_field('local_multipicker_run', 'cancelled', ['id' => $runid]);
        $state = $cancelled ? 'cancelled' : ($more ? 'running' : (
            $DB->record_exists('local_multipicker_operation', ['planid' => $plan->id, 'state' => 'blocked', 'included' => 1])
                ? 'partial' : 'completed'));
        $DB->update_record('local_multipicker_run', (object)[
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
        require_capability('local/multipicker:executemigration', \context_system::instance());
        $DB->set_field('local_multipicker_run', 'cancelled', 1, ['id' => $runid]);
    }
}
