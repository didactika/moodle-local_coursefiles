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
 * Revalidated idempotent migration execution.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\policy\author_policy;
use local_filebank\local\logging\log;

/**
 * Execute one idempotent leaf decision while owning and releasing its destination locks.
 */
final class migration_operation {
    /**
     * Execute.
     *
     * @param int $runid Accountable migration run.
     * @param \stdClass $operation Locked stored operation.
     * @param \stdClass $plan Immutable reviewed plan.
     * @param \local_filebank\local\profile\profile_config $rules Reviewed profile rules.
     */
    public static function execute(
        int $runid,
        \stdClass $operation,
        \stdClass $plan,
        \local_filebank\local\profile\profile_config $rules
    ): void {

        global $DB;
        $preview = json_decode($operation->snapshot, true);
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')
            ->get_lock('destination:' . ($operation->targetcmid ?: 'create:' . $preview['targetkey']), 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        $destinationlock = null;
        try {
            $operation = $DB->get_record('local_filebank_operation', ['id' => $operation->id], '*', MUST_EXIST);
            if ($operation->state !== 'pending') {
                return;
            }
            $snapshot = json_decode($operation->snapshot, true);
            self::source($operation, $plan, $rules, $snapshot);
            $target = $operation->targetcmid ? placement::snapshot($operation->targetcmid)
                : target_binding::resolve($plan->id, $snapshot['targetkey'], $snapshot['target']);
            $targetcmid = $target['cmid'];
            if (!$operation->targetcmid) {
                $destinationlock = target_binding::lock_destination($targetcmid, $target);
            }
            require_capability('moodle/course:manageactivities', \context_module::instance($targetcmid));
            [$source, $sourcecm] = self::recheck($operation, $plan, $rules, $snapshot, $target);
            [$decision, $actual] = self::decision($operation, $snapshot, $source, $targetcmid);
            $outcome = self::outcome($runid, $source, $sourcecm, $target, $decision, $actual);
            $operation->state = 'done';
            $operation->result = json_encode($outcome);
        } catch (\moodle_exception $exception) {
            $operation->state = 'blocked';
            $operation->result = json_encode(['runid' => $runid, 'error' => $exception->errorcode]);
        } finally {
            $operation->timemodified = time();
            $DB->update_record('local_filebank_operation', $operation);
            if ($destinationlock) {
                $destinationlock->release();
            }
            $lock->release();
        }
    }

    /**
     * Source.
     *
     * @param \stdClass $operation Locked stored operation.
     * @param \stdClass $plan Immutable reviewed plan.
     * @param \local_filebank\local\profile\profile_config $rules Reviewed profile rules.
     * @param array $snapshot Reviewed source and destination snapshots.
     */
    private static function source(
        \stdClass $operation,
        \stdClass $plan,
        \local_filebank\local\profile\profile_config $rules,
        array $snapshot
    ): void {

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
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        if (in_array($operation->decision, ['copy', 'overwrite'], true)) {
            author_policy::require_native_copy($source, $sourcecm->course, $snapshot['target']['courseid']);
        }
    }

    /**
     * Recheck.
     *
     * @param \stdClass $operation Locked stored operation.
     * @param \stdClass $plan Immutable reviewed plan.
     * @param \local_filebank\local\profile\profile_config $rules Reviewed profile rules.
     * @param array $snapshot Reviewed source and destination snapshots.
     * @param array $target Current destination snapshot.
     * @return array Result.
     */
    private static function recheck(
        \stdClass $operation,
        \stdClass $plan,
        \local_filebank\local\profile\profile_config $rules,
        array $snapshot,
        array $target
    ): array {
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
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        return [$source, $sourcecm];
    }

    /**
     * Decision.
     *
     * @param \stdClass $operation Locked stored operation.
     * @param array $snapshot Reviewed source and destination snapshots.
     * @param \stored_file $source Reauthorized source leaf.
     * @param int $targetcmid Locked destination module.
     * @return array Result.
     */
    private static function decision(\stdClass $operation, array $snapshot, \stored_file $source, int $targetcmid): array {
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
                throw new \moodle_exception('planstale', 'local_filebank');
            }
            $decision = 'skip_content';
        }
        if ($actual !== $snapshot['existing'] && $decision !== 'skip_identical') {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        if ($operation->decision === 'overwrite' && $decision === 'blocked_content') {
            $decision = 'overwrite';
        }
        return [$decision, $actual];
    }

    /**
     * Copy.
     *
     * @param int $runid Accountable migration run.
     * @param \stored_file $source Reauthorized source leaf.
     * @param \stdClass $sourcecm Live source module.
     * @param array $target Locked destination snapshot.
     * @param string $decision Reviewed copy or overwrite intent.
     * @param array|null $actual Existing destination snapshot.
     * @return array Result.
     */
    private static function copy(
        int $runid,
        \stored_file $source,
        \stdClass $sourcecm,
        array $target,
        string $decision,
        ?array $actual
    ): array {

        global $DB, $USER;
        $targetcmid = $target['cmid'];
        author_policy::require_native_copy($source, $sourcecm->course, $target['courseid']);
        $attributionid = author_policy::capture($source);
        $saved = folder_destination::copy($source, $targetcmid, $decision === 'overwrite', $actual);
        $origin = (object)[
            'pathnamehash' => $saved->get_pathnamehash(), 'attributionid' => $attributionid,
            'contenthash' => $saved->get_contenthash(), 'userid' => $USER->id, 'timecreated' => time(),
        ];
        $previous = $DB->get_field('local_filebank_origin', 'id', ['pathnamehash' => $origin->pathnamehash]);
        if ($previous) {
            $origin->id = $previous;
            $DB->update_record('local_filebank_origin', $origin);
        } else {
            $DB->insert_record('local_filebank_origin', $origin);
        }
        return ['runid' => $runid, 'outcome' => 'copied', 'pathnamehash' => $saved->get_pathnamehash()];
    }

    /**
     * Outcome.
     *
     * @param int $runid Runid.
     * @param \stored_file $source Source.
     * @param \stdClass $sourcecm Sourcecm.
     * @param array $target Target.
     * @param string $decision Decision.
     * @param array|null $actual Current destination snapshot.
     * @return array Result.
     */
    private static function outcome(
        int $runid,
        \stored_file $source,
        \stdClass $sourcecm,
        array $target,
        string $decision,
        ?array $actual
    ): array {
        if ($decision === 'copy' || $decision === 'overwrite') {
            $outcome = self::copy($runid, $source, $sourcecm, $target, $decision, $actual);
        } else if ($decision === 'skip_identical' || $decision === 'skip_content') {
            $outcome = ['runid' => $runid, 'outcome' => 'already_present'];
        } else {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        return $outcome;
    }
}
