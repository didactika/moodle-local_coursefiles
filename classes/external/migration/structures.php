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
 * The structures that the migration functions share.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\external\migration;

use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Describes the parts that more than one function of the migration screens takes or returns, once.
 */
final class structures {
    /**
     * The filters of one side of a migration, as they are sent.
     *
     * @return external_multiple_structure The list of dimension and value.
     */
    public static function filters_parameter(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'key' => new external_value(PARAM_ALPHANUMEXT, 'Key of the filter of the profile'),
                'value' => new external_value(PARAM_TEXT, 'Value that the course has to have; empty for any'),
            ]),
            'Filters of this side',
            VALUE_DEFAULT,
            []
        );
    }

    /**
     * Turn the filters that were sent into the map that the migration is stored with.
     *
     * @param array $filters The list of dimension and value.
     * @return string[] The values by dimension, without the empty ones.
     */
    public static function filters_map(array $filters): array {
        $map = [];
        foreach ($filters as $filter) {
            if ($filter['value'] !== '') {
                $map[$filter['key']] = $filter['value'];
            }
        }
        return $map;
    }

    /**
     * The numbers of what a migration would do.
     *
     * @return external_single_structure The counters and the notes.
     */
    public static function summary(): external_single_structure {
        $numbers = [];
        $counters = ['files', 'tocopy', 'tooverwrite', 'existing', 'skipped', 'included', 'folders', 'sourcecourses',
            'targetcourses', 'different'];
        foreach ($counters as $name) {
            $numbers[$name] = new external_value(PARAM_INT, $name);
        }
        $numbers['haswork'] = new external_value(PARAM_BOOL, 'Whether something would be written');
        $numbers['notes'] = new external_multiple_structure(
            new external_single_structure([
                'kind' => new external_value(PARAM_ALPHANUM, 'What was left out'),
                'count' => new external_value(PARAM_INT, 'How many times'),
                'label' => new external_value(PARAM_TEXT, 'The sentence that says it'),
            ]),
            'What was left out because it did not match; none of it is an error'
        );
        return new external_single_structure($numbers);
    }

    /**
     * A migration with everything its screen needs.
     *
     * @return external_single_structure The migration.
     */
    public static function migration(): external_single_structure {
        $side = new external_multiple_structure(
            new external_single_structure([
                'key' => new external_value(PARAM_ALPHANUMEXT, 'Key of the filter'),
                'label' => new external_value(PARAM_TEXT, 'Name of the dimension'),
                'value' => new external_value(PARAM_TEXT, 'Value that was asked for'),
            ]),
            'Filters of one side'
        );
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Migration'),
            'uuid' => new external_value(PARAM_RAW_TRIMMED, 'Reference'),
            'profileid' => new external_value(PARAM_INT, 'Profile'),
            'profile' => new external_value(PARAM_TEXT, 'Name of the profile'),
            'state' => new external_value(PARAM_ALPHANUMEXT, 'State'),
            'statelabel' => new external_value(PARAM_TEXT, 'State, written'),
            'digest' => new external_value(PARAM_ALPHANUM, 'What the person is looking at'),
            'timecreated' => new external_value(PARAM_INT, 'Created'),
            'datelabel' => new external_value(PARAM_TEXT, 'Created, written'),
            'editable' => new external_value(PARAM_BOOL, 'Whether it can still be changed'),
            'outdated' => new external_value(PARAM_BOOL, 'Whether the profile changed after the migration was worked out'),
            'canexecute' => new external_value(PARAM_BOOL, 'Whether the viewer can start it'),
            'summary' => self::summary(),
            'source' => new external_value(PARAM_TEXT, 'Where it comes from, written'),
            'target' => new external_value(PARAM_TEXT, 'Where it goes, written'),
            'filters' => new external_single_structure(['source' => $side, 'target' => $side], 'The filters'),
            'rules' => new external_value(PARAM_RAW, 'The rules as the screens edit them, as JSON'),
            'run' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Run'),
                'state' => new external_value(PARAM_ALPHANUMEXT, 'State'),
                'statelabel' => new external_value(PARAM_TEXT, 'State, written'),
                'done' => new external_value(PARAM_INT, 'Files processed'),
                'total' => new external_value(PARAM_INT, 'Files to process'),
                'active' => new external_value(PARAM_BOOL, 'Whether it is queued or running'),
                'cancancel' => new external_value(PARAM_BOOL, 'Whether the viewer can stop it'),
            ], 'The last run', VALUE_OPTIONAL),
        ]);
    }
}
