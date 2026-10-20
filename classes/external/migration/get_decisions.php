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
 * External function: a page of what waits for a decision.
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
use local_filebank\local\migration\view\migration_decisions;

// The classes below extend those of core_external, which Moodle 4.0 and 4.1 only have under their old names.
// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalGlobalState
\local_filebank\compat\external_aliases::register();

/**
 * The files in conflict, or the issues, of one nature, a page at a time.
 */
class get_decisions extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'kind' => new external_value(PARAM_ALPHA, 'file or issue'),
            'nature' => new external_value(PARAM_ALPHANUMEXT, 'Decision of the files or reason of the issues'),
            'page' => new external_value(PARAM_INT, 'Zero-based page', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Size of the page', VALUE_DEFAULT, 25),
        ]);
    }

    /**
     * Read a page of what waits for a decision.
     *
     * @param int $id Migration.
     * @param string $kind file or issue.
     * @param string $nature Decision of the files or reason of the issues.
     * @param int $page Zero-based page.
     * @param int $perpage Size of the page.
     * @return array The total and the items.
     */
    public static function execute(int $id, string $kind, string $nature, int $page = 0, int $perpage = 25): array {
        $params = self::validate_parameters(self::execute_parameters(), compact('id', 'kind', 'nature', 'page', 'perpage'));
        self::validate_context(\context_system::instance());
        if (!in_array($params['kind'], ['file', 'issue'], true)) {
            throw new \invalid_parameter_exception('Unknown decision kind');
        }
        return migration_decisions::page($params['id'], $params['kind'], $params['nature'], $params['page'], $params['perpage']);
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'How many there are of this nature'),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Operation, or position of the issue'),
                    'label' => new external_value(PARAM_TEXT, 'What it is'),
                    'context' => new external_value(PARAM_TEXT, 'Where it is'),
                    'detail' => new external_value(PARAM_TEXT, 'More about it'),
                    'choices' => structures::choices(),
                ]),
                'The items of the page'
            ),
        ]);
    }
}
