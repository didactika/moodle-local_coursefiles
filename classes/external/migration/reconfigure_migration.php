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
 * External function: change the parameters of a migration.
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
use local_filebank\local\migration\migration_rules;
use local_filebank\local\migration\plan_editor;
use local_filebank\local\migration\view\migration_overview;

/**
 * Changes the courses, the correspondences and the policies of a migration that has not run, and works it out again where it is.
 */
class reconfigure_migration extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'digest' => new external_value(PARAM_ALPHANUM, 'What the person is looking at'),
            'sourcefilters' => structures::filters_parameter(),
            'targetfilters' => structures::filters_parameter(),
            'rules' => new external_value(PARAM_RAW, 'The rules as the screens edit them, as JSON'),
        ]);
    }

    /**
     * Change the parameters.
     *
     * @param int $id Migration.
     * @param string $digest What the person is looking at.
     * @param array $sourcefilters Filters of the source courses.
     * @param array $targetfilters Filters of the destination courses.
     * @param string $rules The rules, as JSON.
     * @return array The migration as it is now.
     */
    public static function execute(int $id, string $digest, array $sourcefilters, array $targetfilters, string $rules): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('id', 'digest', 'sourcefilters', 'targetfilters', 'rules')
        );
        self::validate_context(\context_system::instance());
        $source = structures::filters_map($params['sourcefilters']);
        $target = structures::filters_map($params['targetfilters']);
        plan_editor::reconfigure(
            $params['id'],
            $params['digest'],
            implode(' / ', $source),
            implode(' / ', $target),
            '',
            ['migration' => migration_rules::from_ui(check_migration::decode($params['rules'])),
                'filters' => ['source' => $source, 'target' => $target]]
        );
        return migration_overview::get($params['id']);
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return structures::migration();
    }
}
