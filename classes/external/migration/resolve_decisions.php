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
 * External function: settle what waits for a decision.
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
use local_filebank\local\migration\plan_editor;
use local_filebank\local\migration\view\migration_overview;

/**
 * Settles some of the open decisions of a migration, or all of one nature, inside the same migration.
 */
class resolve_decisions extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'digest' => new external_value(PARAM_ALPHANUM, 'What the person is looking at'),
            'kind' => new external_value(PARAM_ALPHA, 'file or issue'),
            'nature' => new external_value(PARAM_ALPHANUMEXT, 'Nature to settle when no ids are given', VALUE_DEFAULT, ''),
            'ids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Operation, or position of the issue'),
                'What to settle; empty for everything of the nature',
                VALUE_DEFAULT,
                []
            ),
            'choice' => new external_value(PARAM_RAW_TRIMMED, 'overwrite or skip for a file; exclude, folder:ID or create:ID'),
        ]);
    }

    /**
     * Settle decisions.
     *
     * @param int $id Migration.
     * @param string $digest What the person is looking at.
     * @param string $kind file or issue.
     * @param string $nature Nature to settle when no ids are given.
     * @param int[] $ids What to settle.
     * @param string $choice The choice.
     * @return array How many were settled and the migration as it is now.
     */
    public static function execute(int $id, string $digest, string $kind, string $nature, array $ids, string $choice): array {
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('id', 'digest', 'kind', 'nature', 'ids', 'choice')
        );
        self::validate_context(\context_system::instance());
        $changed = plan_editor::resolve(
            $params['id'],
            $params['digest'],
            $params['kind'],
            $params['ids'],
            $params['choice'],
            $params['nature']
        );
        return ['changed' => $changed, 'migration' => migration_overview::get($params['id'])];
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'changed' => new external_value(PARAM_INT, 'How many were settled'),
            'migration' => structures::migration(),
        ]);
    }
}
