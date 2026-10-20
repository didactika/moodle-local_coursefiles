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
 * External function: a page of the migrations of the site.
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
use local_filebank\local\migration\view\migration_catalog;

// The classes below extend those of core_external, which Moodle 4.0 and 4.1 only have under their old names.
// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalGlobalState
\local_filebank\compat\external_aliases::register();

/**
 * The list of migrations, filtered and paged by the server.
 */
class list_migrations extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(PARAM_TEXT, 'Part of the name of the profile', VALUE_DEFAULT, ''),
            'state' => new external_value(PARAM_ALPHANUMEXT, 'State to show; empty for all', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Zero-based page', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Size of the page', VALUE_DEFAULT, 10),
        ]);
    }

    /**
     * Read a page of migrations.
     *
     * @param string $search Part of the name of the profile.
     * @param string $state State to show.
     * @param int $page Zero-based page.
     * @param int $perpage Size of the page.
     * @return array The total, the count of each state and the migrations of the page.
     */
    public static function execute(string $search = '', string $state = '', int $page = 0, int $perpage = 10): array {
        $params = self::validate_parameters(self::execute_parameters(), compact('search', 'state', 'page', 'perpage'));
        self::validate_context(\context_system::instance());
        $result = migration_catalog::page($params['search'], $params['state'], $params['page'], $params['perpage']);
        $result['counts'] = array_map(
            static fn(string $key, int $count): array => ['state' => $key, 'count' => $count],
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
            'total' => new external_value(PARAM_INT, 'Migrations that match'),
            'counts' => new external_multiple_structure(
                new external_single_structure([
                    'state' => new external_value(PARAM_ALPHANUMEXT, 'State, or all'),
                    'count' => new external_value(PARAM_INT, 'Migrations in that state'),
                ]),
                'Migrations of each state for the search'
            ),
            'migrations' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Migration'),
                    'profile' => new external_value(PARAM_TEXT, 'Name of the profile'),
                    'source' => new external_value(PARAM_TEXT, 'Where it comes from'),
                    'target' => new external_value(PARAM_TEXT, 'Where it goes'),
                    'state' => new external_value(PARAM_ALPHANUMEXT, 'State'),
                    'statelabel' => new external_value(PARAM_TEXT, 'State, written'),
                    'timecreated' => new external_value(PARAM_INT, 'Created'),
                    'datelabel' => new external_value(PARAM_TEXT, 'Created, written'),
                    'files' => new external_value(PARAM_INT, 'Files it looks at'),
                    'towrite' => new external_value(PARAM_INT, 'Files it would write'),
                    'conflicts' => new external_value(PARAM_INT, 'Files that wait for a decision'),
                    'done' => new external_value(PARAM_INT, 'Files processed'),
                    'candelete' => new external_value(PARAM_BOOL, 'Whether it can be deleted'),
                ]),
                'The migrations of the page'
            ),
        ]);
    }
}
