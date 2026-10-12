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
 * Creates explicit file decisions without writing course materials.
 */
final class migration_planner {
    /**
     * Preview copies between two values of a configured course dimension.
     *
     * @param int $profileid Profile.
     * @param string $sourceedition Source label.
     * @param string $targetedition Target label.
     * @param bool $layoutconfirmed Reviewed section layout.
     * @param string $scopekey Configured capture filter or edition.
     * @param array $options Migration-owned rules and capture filters.
     * @return \stdClass Stored plan.
     */
    public static function preview(
        int $profileid,
        string $sourceedition,
        string $targetedition,
        bool $layoutconfirmed,
        string $scopekey = 'edition',
        array $options = []
    ): \stdClass {

        require_capability('local/filebank:planmigration', \context_system::instance());
        [$profile, $revision, $rules] = profiles::load($profileid);
        $scopecapture = $scopekey !== '' ? course_scope::capture($rules, $scopekey) : '';
        $config = $options['migration'] ?? $rules->get_config()['migration'] ?? [];
        $placements = $options['placements'] ?? [];
        $excluded = $options['excluded'] ?? [];
        $ignoredissues = $options['ignoredissues'] ?? [];
        $filechoices = $options['filechoices'] ?? [];
        $filters = $options['filters'] ?? null;
        $filters = migration_pairs::filters($filters, $rules->filter_definitions());
        if (
            ($filters === null && ($sourceedition === '' || $targetedition === '' || $sourceedition === $targetedition))
                || (($config['section_match']['strategy'] ?? 'ordinal') === 'ordinal' && !$layoutconfirmed)
        ) {
            throw new \moodle_exception('layoutrequired', 'local_filebank');
        }
        $pairs = migration_pairs::collect(
            $revision,
            $rules,
            $scopekey,
            $scopecapture,
            $sourceedition,
            $targetedition,
            $filters
        );
        $operations = [];
        $issues = [];
        $creations = [];
        $request = compact('profileid', 'config', 'placements', 'filechoices', 'excluded');
        $request['loaded'] = [$profile, $revision, $rules];
        foreach ($pairs as $pair) {
            planning_materials::pair($pair, $request, $operations, $issues, $creations);
        }
        $issues = array_values(
            array_filter(
                $issues,
                static fn($issue) => !in_array(
                    hash('sha256', json_encode($issue)),
                    $ignoredissues,
                    true
                )
            )
        );
        $document = ['sourceedition' => $sourceedition, 'targetedition' => $targetedition, 'scopekey' => $scopekey,
            'layoutconfirmed' => $layoutconfirmed, 'migration' => $config, 'filters' => $filters,
            'placements' => $placements, 'excluded' => $excluded, 'ignoredissues' => $ignoredissues,
            'filechoices' => $filechoices, 'issues' => $issues, 'operationcount' => count($operations)];
        return planning_store::save($profileid, (int)$revision->id, $document, $operations, $creations);
    }
}
