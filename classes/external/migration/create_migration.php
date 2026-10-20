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
 * External function: create a migration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\external\migration;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_filebank\local\migration\migration_planner;
use local_filebank\local\migration\migration_rules;

/**
 * Works a migration out and stores it, so that it can be reviewed and started.
 */
class create_migration extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return check_migration::execute_parameters();
    }

    /**
     * Create the migration.
     *
     * @param int $profileid Profile.
     * @param array $sourcefilters Filters of the source courses.
     * @param array $targetfilters Filters of the destination courses.
     * @param string $rules The rules, as JSON.
     * @return array The migration that was created.
     */
    public static function execute(int $profileid, array $sourcefilters, array $targetfilters, string $rules): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('profileid', 'sourcefilters', 'targetfilters', 'rules')
        );
        self::validate_context(\context_system::instance());
        $source = structures::filters_map($params['sourcefilters']);
        $target = structures::filters_map($params['targetfilters']);
        $plan = migration_planner::preview(
            $params['profileid'],
            implode(' / ', $source),
            implode(' / ', $target),
            '',
            ['migration' => migration_rules::from_ui(check_migration::decode($params['rules'])),
                'filters' => ['source' => $source, 'target' => $target]]
        );
        return ['id' => (int) $plan->id];
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(['id' => new external_value(PARAM_INT, 'The new migration')]);
    }
}
