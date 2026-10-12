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
 * Reviewed immutable family migration planning.
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
 * Persist an immutable reviewed plan and its operation manifest atomically.
 */
final class planning_store {
    /**
     * Persist the reviewed plan and complete immutable operation manifest.
     *
     * @param int $profileid Profileid.
     * @param int $revisionid Revisionid.
     * @param array $document Document.
     * @param array $operations Operations.
     * @param array $creations Creations.
     * @return stdClass Result.
     */
    public static function save(
        int $profileid,
        int $revisionid,
        array $document,
        array $operations,
        array $creations
    ): \stdClass {
        global $DB, $USER;
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $blockedissues = array_filter($document['issues'], static fn($issue) => $issue['blocking'] ?? true);
        $blockedfiles = array_filter(
            $operations,
            static fn($operation) => $operation['included']
                && str_starts_with($operation['decision'], 'blocked_')
        );
        $blocked = $blockedissues || $blockedfiles;
        $haswork = array_filter(
            $operations,
            static fn($op) => $op['included']
                && in_array($op['decision'], ['copy', 'overwrite'], true)
        );
        $transaction = $DB->start_delegated_transaction();
        $planid = $DB->insert_record(
            'local_filebank_plan',
            (object)[
                'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revisionid,
                'userid' => $USER->id, 'configjson' => $json, 'digest' => hash('sha256', $json),
                'state' => $blocked ? 'blocked' : ($haswork ? 'reviewable' : 'cancelled'), 'timecreated' => time(),
            ]
        );
        foreach ($creations as $key => $definition) {
            $DB->insert_record(
                'local_filebank_target',
                (object)['planid' => $planid, 'targetkey' => $key,
                    'definitionjson' => json_encode($definition), 'cmid' => 0, 'snapshotjson' => '{}']
            );
        }
        foreach ($operations as $operation) {
            $DB->insert_record(
                'local_filebank_operation',
                (object)[
                    'planid' => $planid, 'opkey' => $operation['opkey'], 'sourcehash' => $operation['sourcehash'],
                    'targetcmid' => $operation['targetcmid'], 'sourcecourseid' => $operation['snapshot']['sourcecourse'],
                    'targetcourseid' => $operation['snapshot']['target']['courseid'],
                    'sourcecmid' => $operation['snapshot']['sourcecmid'], 'snapshot' => json_encode($operation['snapshot']),
                    'included' => $operation['included'] ? 1 : 0,
                    'decision' => $operation['decision'], 'state' => 'pending', 'result' => '{}', 'timemodified' => time(),
                ]
            );
        }
        $document['operationsdigest'] = plan_selection::manifest_digest($planid);
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $DB->update_record(
            'local_filebank_plan',
            (object)[
                'id' => $planid, 'configjson' => $json, 'digest' => hash('sha256', $json),
            ]
        );
        $transaction->allow_commit();
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
        log::record('migration_previewed', $plan->uuid, ['operations' => count($operations), 'blocked' => (bool)$blocked]);
        return $plan;
    }
}
