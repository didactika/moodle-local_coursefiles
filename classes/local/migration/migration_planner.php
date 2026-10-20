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
 * Family migration planning.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\profile\profiles;

/**
 * Works out what a migration would do, without writing course materials: the files that would be copied, the ones that already are
 * there, and the few that need a decision. What does not match is left out and counted, and nothing that needs a decision stops the
 * rest from being migrated.
 */
final class migration_planner {
    /**
     * Preview copies between two values of a configured course dimension and store the result as a new migration.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param string $scopekey Filter dimension used for the edition-like labels.
     * @param array $options Reviewed migration, filters and exclusions.
     * @return \stdClass Stored plan.
     */
    public static function preview(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        string $scopekey = 'edition',
        array $options = []
    ): \stdClass {
        $draft = self::compute($profileid, $sourceedition, $targetedition, $scopekey, $options);
        return planning_store::save(
            $profileid,
            (int) $draft['revision']->id,
            $draft['document'],
            $draft['operations'],
            $draft['creations']
        );
    }

    /**
     * Work a stored migration out again from its document and store the result where it is, without creating another one.
     *
     * @param int $planid The migration to rewrite.
     * @param int $profileid Profile.
     * @param array $document Document of the migration with the changes already made.
     * @return \stdClass The stored migration.
     */
    public static function replace(int $planid, int $profileid, array $document): \stdClass {
        $draft = self::compute(
            $profileid,
            $document['sourceedition'],
            $document['targetedition'],
            $document['scopekey'] ?? 'edition',
            [
                'migration' => $document['migration'], 'filters' => $document['filters'] ?? null,
                'excluded' => $document['excluded'] ?? [],
            ]
        );
        return planning_store::save(
            $profileid,
            (int) $draft['revision']->id,
            $draft['document'],
            $draft['operations'],
            $draft['creations'],
            $planid
        );
    }

    /**
     * Work out what a migration would do and say it in numbers, without storing anything.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param string $scopekey Filter dimension used for the edition-like labels.
     * @param array $options Reviewed migration, filters and exclusions.
     * @return array What it would copy, overwrite, skip and ask about, and what it leaves out.
     */
    public static function check(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        string $scopekey = 'edition',
        array $options = []
    ): array {
        $draft = self::compute($profileid, $sourceedition, $targetedition, $scopekey, $options);
        return migration_summary::summarise($draft['document'], $draft['operations']);
    }

    /**
     * Work out the operations, the destinations to create, the decisions that are open and the notes of a migration.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param string $scopekey Filter dimension used for the edition-like labels.
     * @param array $options Reviewed migration, filters and exclusions.
     * @return array The revision, the document of the migration, its operations and the destinations to create.
     */
    public static function compute(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        string $scopekey = 'edition',
        array $options = []
    ): array {
        require_capability('local/filebank:planmigration', \context_system::instance());
        [$profile, $revision, $rules] = profiles::load($profileid);
        $scopecapture = $scopekey !== '' ? course_scope::capture($rules, $scopekey) : '';
        $config = $options['migration'] ?? $rules->get_config()['migration'] ?? [];
        $excluded = $options['excluded'] ?? [];
        $filters = migration_pairs::filters($options['filters'] ?? null, $rules->filter_definitions());
        if ($filters === null && ($sourceedition === '' || $targetedition === '' || $sourceedition === $targetedition)) {
            throw new \moodle_exception('layoutrequired', 'local_filebank');
        }
        $pairs = migration_pairs::collect($revision, $rules, $scopekey, $scopecapture, $sourceedition, $targetedition, $filters);
        $operations = [];
        $creations = [];
        $notes = new migration_notes();
        $request = compact('profileid', 'config', 'excluded', 'notes');
        $request['loaded'] = [$profile, $revision, $rules];
        foreach ($pairs as $pair) {
            planning_materials::pair($pair, $request, $operations, $creations);
        }
        $document = ['sourceedition' => $sourceedition, 'targetedition' => $targetedition, 'scopekey' => $scopekey,
            'layoutconfirmed' => true, 'migration' => $config, 'filters' => $filters,
            'excluded' => $excluded, 'notes' => $notes->counts(),
            'operationcount' => count($operations)];
        return ['profile' => $profile, 'revision' => $revision, 'document' => $document,
            'operations' => $operations, 'creations' => $creations];
    }
}
