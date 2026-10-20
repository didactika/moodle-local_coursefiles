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
 * Persistence of what a migration would do.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\logging\log;

/**
 * Stores a migration with its operations and the destinations to create, as a new migration or in place of what an existing one
 * had.
 */
final class planning_store {
    /**
     * Store a migration.
     *
     * A migration that already exists and has not run is rewritten where it is: its identity, its number and its date stay, and the
     * operations and the document are the new ones. Anything that is not a new migration goes through here, so a decision that is
     * taken inside a migration never leaves a second one behind.
     *
     * @param int $profileid Profile.
     * @param int $revisionid Reviewed profile revision.
     * @param array $document Document of the migration.
     * @param array[] $operations The operations.
     * @param array[] $creations The destinations that would be created.
     * @param int $replaceid The migration to rewrite, or 0 for a new one.
     * @return \stdClass Stored migration.
     */
    public static function save(
        int $profileid,
        int $revisionid,
        array $document,
        array $operations,
        array $creations,
        int $replaceid = 0
    ): \stdClass {
        global $DB, $USER;
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $transaction = $DB->start_delegated_transaction();
        if ($replaceid > 0) {
            $planid = $replaceid;
            $DB->delete_records('local_filebank_target', ['planid' => $planid]);
            $DB->delete_records('local_filebank_operation', ['planid' => $planid]);
            $DB->update_record('local_filebank_plan', (object) [
                'id' => $planid, 'revisionid' => $revisionid, 'configjson' => $json, 'digest' => hash('sha256', $json),
                'state' => 'reviewable',
            ]);
        } else {
            $planid = $DB->insert_record('local_filebank_plan', (object) [
                'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revisionid,
                'userid' => $USER->id, 'configjson' => $json, 'digest' => hash('sha256', $json),
                'state' => 'reviewable', 'timecreated' => time(),
            ]);
        }
        self::rows($planid, $operations, $creations);
        $document['operationsdigest'] = plan_selection::manifest_digest($planid);
        $document['selectiondigest'] = plan_selection::digest($planid);
        $json = json_encode($document, JSON_UNESCAPED_SLASHES);
        $DB->update_record('local_filebank_plan', (object) [
            'id' => $planid, 'configjson' => $json, 'digest' => hash('sha256', $json),
        ]);
        $transaction->allow_commit();
        $plan = $DB->get_record('local_filebank_plan', ['id' => $planid], '*', MUST_EXIST);
        log::record($replaceid > 0 ? 'migration_updated' : 'migration_previewed', $plan->uuid, [
            'operations' => count($operations),
            'conflicts' => count(array_filter(
                $operations,
                static fn(array $operation): bool => str_starts_with($operation['decision'], 'blocked_')
            )),
        ]);
        return $plan;
    }

    /**
     * Write the operations and the destinations to create of a migration.
     *
     * @param int $planid The migration.
     * @param array[] $operations The operations.
     * @param array[] $creations The destinations that would be created.
     */
    private static function rows(int $planid, array $operations, array $creations): void {
        global $DB;
        foreach ($creations as $key => $definition) {
            $DB->insert_record('local_filebank_target', (object) ['planid' => $planid, 'targetkey' => $key,
                'definitionjson' => json_encode($definition), 'cmid' => 0, 'snapshotjson' => '{}']);
        }
        foreach ($operations as $operation) {
            $DB->insert_record('local_filebank_operation', (object) [
                'planid' => $planid, 'opkey' => $operation['opkey'], 'sourcehash' => $operation['sourcehash'],
                'targetcmid' => $operation['targetcmid'], 'sourcecourseid' => $operation['snapshot']['sourcecourse'],
                'targetcourseid' => $operation['snapshot']['target']['courseid'],
                'sourcecmid' => $operation['snapshot']['sourcecmid'], 'snapshot' => json_encode($operation['snapshot']),
                'included' => $operation['included'] ? 1 : 0,
                'decision' => $operation['decision'], 'state' => 'pending', 'result' => '{}', 'timemodified' => time(),
            ]);
        }
    }
}
