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
 * Revalidate a reviewed plan and its selection manifest under the caller-owned locks.
 */
final class migration_queue {
    /**
     * Validate.
     *
     * @param \stdClass $plan Locked reviewed plan.
     * @param string $digest Submitted reviewed digest.
     * @return bool Result.
     */
    public static function validate(\stdClass $plan, string $digest): bool {

        global $DB;
        self::reviewed($plan, $digest);
        if (!plan_history::has_work($plan->id)) {
            // Nothing to write: the migration stays as it is so its parameters can still be changed.
            log::record('migration_empty_skipped', $plan->uuid, []);
            return false;
        }
        if (
            !$DB->record_exists('local_filebank_operation', ['planid' => $plan->id, 'included' => 1, 'state' => 'pending'])
                || $DB->record_exists_select(
                    'local_filebank_run',
                    'planid = ? AND (state = ? OR state = ?)',
                    [$plan->id, 'queued', 'running']
                )
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        return true;
    }

    /**
     * Reviewed.
     *
     * @param \stdClass $plan Locked reviewed plan.
     * @param string $digest Submitted reviewed digest.
     */
    private static function reviewed(\stdClass $plan, string $digest): void {
        [, $revision] = profiles::load($plan->profileid);
        if ((int)$revision->id !== (int)$plan->revisionid) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        if (
            $plan->state !== 'reviewable' || !hash_equals($plan->digest, $digest)
                || !hash_equals($plan->digest, hash('sha256', $plan->configjson))
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        self::manifest($plan);
    }

    /**
     * Manifest.
     *
     * @param \stdClass $plan Locked reviewed plan.
     */
    private static function manifest(\stdClass $plan): void {
        $document = json_decode($plan->configjson, true);
        if (
            isset($document['operationsdigest'])
                && !hash_equals($document['operationsdigest'], plan_selection::manifest_digest($plan->id))
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        if (
            isset($document['selectiondigest'])
                && !hash_equals($document['selectiondigest'], plan_selection::digest($plan->id))
        ) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
    }
}
