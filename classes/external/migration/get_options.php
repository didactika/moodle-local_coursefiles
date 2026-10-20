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
 * External function: what a new migration can be asked to do.
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

/**
 * The profiles that can migrate, the filters each one has and the rules to start from.
 */
class get_options extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(PARAM_TEXT, 'Part of the name of the profiles to offer', VALUE_DEFAULT, ''),
            'profileid' => new external_value(PARAM_INT, 'The profile that is already chosen', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Read the options.
     *
     * @param string $search Part of the name of the profiles to offer.
     * @param int $profileid The profile that is already chosen.
     * @return array The profiles, the kinds of source a rule can read and the earlier migrations of the chosen profile.
     */
    public static function execute(string $search = '', int $profileid = 0): array {
        ['search' => $search, 'profileid' => $profileid] = self::validate_parameters(
            self::execute_parameters(),
            ['search' => $search, 'profileid' => $profileid]
        );
        self::validate_context(\context_system::instance());
        return migration_catalog::options($search, $profileid);
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'profiles' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Profile'),
                    'name' => new external_value(PARAM_TEXT, 'Name of the profile'),
                    'dimensions' => new external_multiple_structure(
                        new external_single_structure([
                            'key' => new external_value(PARAM_ALPHANUMEXT, 'Key of the filter'),
                            'label' => new external_value(PARAM_TEXT, 'Name of the dimension'),
                        ]),
                        'The filters that can narrow the courses of a side'
                    ),
                    'rules' => new external_value(PARAM_RAW, 'The rules of the profile as the screens edit them, as JSON'),
                ]),
                'The profiles that are enabled'
            ),
            'modules' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_ALPHANUMEXT, 'Kind of source'),
                    'label' => new external_value(PARAM_TEXT, 'Name of the kind of source'),
                ]),
                'What a correspondence can read'
            ),
            'previous' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Migration'),
                    'label' => new external_value(PARAM_TEXT, 'A short name of the migration'),
                ]),
                'The last migrations of the chosen profile'
            ),
        ]);
    }
}
