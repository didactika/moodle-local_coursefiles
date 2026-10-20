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
 * External function: a page of the files of a migration.
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
 * The files of a migration, filtered, searched and paged by the server.
 */
class get_files extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'bucket' => new external_value(PARAM_ALPHA, 'Group of files; empty for all', VALUE_DEFAULT, ''),
            'search' => new external_value(PARAM_TEXT, 'Part of a file, folder or course name', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Zero-based page', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Size of the page', VALUE_DEFAULT, 25),
        ]);
    }

    /**
     * Read a page of files.
     *
     * @param int $id Migration.
     * @param string $bucket Group of files.
     * @param string $search Part of a name.
     * @param int $page Zero-based page.
     * @param int $perpage Size of the page.
     * @return array The counts of every group, the total of the filter and the files.
     */
    public static function execute(int $id, string $bucket = '', string $search = '', int $page = 0, int $perpage = 25): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('id', 'bucket', 'search', 'page', 'perpage')
        );
        self::validate_context(\context_system::instance());
        $result = migration_files::page(
            $params['id'],
            $params['bucket'],
            $params['search'],
            $params['page'],
            $params['perpage']
        );
        $result['counts'] = array_map(
            static fn(string $key, int $count): array => ['bucket' => $key, 'count' => $count],
            array_keys($result['counts']),
            $result['counts']
        );
        return $result;
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'counts' => new external_multiple_structure(
                new external_single_structure([
                    'bucket' => new external_value(PARAM_ALPHA, 'Group of files, or all'),
                    'count' => new external_value(PARAM_INT, 'Files in the group'),
                ]),
                'How many files each group has'
            ),
            'total' => new external_value(PARAM_INT, 'Files that match the filter'),
            'files' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Operation'),
                    'filename' => new external_value(PARAM_TEXT, 'Name of the file'),
                    'filepath' => new external_value(PARAM_TEXT, 'Path of the file in its folder'),
                    'sourcecourse' => new external_value(PARAM_TEXT, 'Course it comes from'),
                    'sourcefolder' => new external_value(PARAM_TEXT, 'Folder it comes from'),
                    'targetcourse' => new external_value(PARAM_TEXT, 'Course it goes to'),
                    'targetfolder' => new external_value(PARAM_TEXT, 'Folder it goes to'),
                    'decision' => new external_value(PARAM_ALPHANUMEXT, 'What would be done'),
                    'decisionlabel' => new external_value(PARAM_TEXT, 'What would be done, written'),
                    'included' => new external_value(PARAM_BOOL, 'Whether it is part of the migration'),
                    'settable' => new external_value(PARAM_BOOL, 'Whether it can be left in or out'),
                    'state' => new external_value(PARAM_ALPHANUMEXT, 'State'),
                    'statelabel' => new external_value(PARAM_TEXT, 'State, written'),
                    'result' => new external_value(PARAM_TEXT, 'How it went'),
                    'author' => new external_value(PARAM_TEXT, 'Author'),
                    'license' => new external_value(PARAM_TEXT, 'Licence'),
                ]),
                'The files of the page'
            ),
        ]);
    }
}
