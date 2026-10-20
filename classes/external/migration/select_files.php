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
 * External function: choose which files are part of a migration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\external\migration;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_filebank\local\migration\plan_selection;
use local_filebank\local\migration\view\migration_overview;

/**
 * Puts files in or out of a migration: the ones on a page, every file, none, or every file of a folder.
 */
class select_files extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        $ids = static fn(string $description): external_multiple_structure => new external_multiple_structure(
            new external_value(PARAM_INT, 'Operation'),
            $description,
            VALUE_DEFAULT,
            []
        );
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'digest' => new external_value(PARAM_ALPHANUM, 'What the person is looking at'),
            'action' => new external_value(PARAM_ALPHA, 'page, all, none or group'),
            'visible' => $ids('The files of the page that was on screen'),
            'included' => $ids('The ones of those that are part of the migration'),
            'groupcourseid' => new external_value(PARAM_INT, 'Course of the folder, for a group', VALUE_DEFAULT, 0),
            'groupcmid' => new external_value(PARAM_INT, 'Folder, for a group; 0 for the whole course', VALUE_DEFAULT, 0),
            'groupincluded' => new external_value(PARAM_INT, '1 to put the group in, 0 to take it out', VALUE_DEFAULT, 1),
        ]);
    }

    /**
     * Change the selection.
     *
     * @param int $id Migration.
     * @param string $digest What the person is looking at.
     * @param string $action page, all, none or group.
     * @param int[] $visible The files of the page that was on screen.
     * @param int[] $included The ones of those that are part of the migration.
     * @param int $groupcourseid Course of the folder.
     * @param int $groupcmid Folder.
     * @param int $groupincluded 1 to put the group in, 0 to take it out.
     * @return array The migration as it is now.
     */
    public static function execute(
        int $id,
        string $digest,
        string $action,
        array $visible = [],
        array $included = [],
        int $groupcourseid = 0,
        int $groupcmid = 0,
        int $groupincluded = 1
    ): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('id', 'digest', 'action', 'visible', 'included', 'groupcourseid', 'groupcmid', 'groupincluded')
        );
        self::validate_context(\context_system::instance());
        plan_selection::save(
            $params['id'],
            $params['digest'],
            $params['visible'],
            $params['included'],
            $params['action'],
            ['courseid' => $params['groupcourseid'], 'cmid' => $params['groupcmid'], 'included' => $params['groupincluded']]
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
