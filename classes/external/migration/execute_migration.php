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
 * External function: start a migration.
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
use local_filebank\local\migration\migration_executor;
use local_filebank\local\migration\view\migration_overview;

/**
 * Queues the run of a migration that was reviewed, and gives the migration back so that its progress can be followed.
 */
class execute_migration extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'digest' => new external_value(PARAM_ALPHANUM, 'What the person reviewed'),
        ]);
    }

    /**
     * Queue the run.
     *
     * @param int $id Migration.
     * @param string $digest What the person reviewed.
     * @return array The migration as it is now.
     */
    public static function execute(int $id, string $digest): array {
        $params = self::validate_parameters(self::execute_parameters(), compact('id', 'digest'));
        self::validate_context(\context_system::instance());
        migration_executor::queue($params['id'], $params['digest']);
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
