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
 * External function: a page of the folders of a migration.
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
use local_filebank\local\migration\view\migration_files;

// The classes below extend those of core_external, which Moodle 4.0 and 4.1 only have under their old names.
// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalGlobalState
\local_filebank\compat\external_aliases::register();

/**
 * The folders that a migration reads from and writes to, with how many of their files are part of it.
 */
class get_folders extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'search' => new external_value(PARAM_TEXT, 'Part of a course name', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Zero-based page', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Size of the page', VALUE_DEFAULT, 25),
        ]);
    }

    /**
     * Read a page of folders.
     *
     * @param int $id Migration.
     * @param string $search Part of a name.
     * @param int $page Zero-based page.
     * @param int $perpage Size of the page.
     * @return array The total and the folders.
     */
    public static function execute(int $id, string $search = '', int $page = 0, int $perpage = 25): array {
        $params = self::validate_parameters(self::execute_parameters(), compact('id', 'search', 'page', 'perpage'));
        self::validate_context(\context_system::instance());
        return migration_files::folders($params['id'], $params['search'], $params['page'], $params['perpage']);
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Folders that match'),
            'folders' => new external_multiple_structure(
                new external_single_structure([
                    'sourcecourseid' => new external_value(PARAM_INT, 'Course it comes from'),
                    'sourcecmid' => new external_value(PARAM_INT, 'Folder it comes from'),
                    'sourcecourse' => new external_value(PARAM_TEXT, 'Course it comes from, written'),
                    'sourcefolder' => new external_value(PARAM_TEXT, 'Folder it comes from, written'),
                    'targetcourse' => new external_value(PARAM_TEXT, 'Course it goes to'),
                    'targetfolder' => new external_value(PARAM_TEXT, 'Folder it goes to'),
                    'total' => new external_value(PARAM_INT, 'Files it has'),
                    'included' => new external_value(PARAM_INT, 'Files that are part of the migration'),
                ]),
                'The folders of the page'
            ),
        ]);
    }
}
