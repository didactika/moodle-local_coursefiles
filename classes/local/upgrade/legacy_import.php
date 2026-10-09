<?php
// This file is part of Moodle - http://moodle.org/
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
 * Preserve CourseBridge installations when adopting the MultiPicker component names.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\upgrade;

/**

 * Imports legacy data once; legacy tables remain available for a controlled rollback.

 */
final class legacy_import {
    /**
     * Import the complete old installation after XMLDB creates the new tables.
     */
    public static function execute(): void {
        global $DB;
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists(new \xmldb_table('local_coursebridge'))) {
            return;
        }
        $tables = ['', '_revision', '_course', '_file', '_coursepolicy', '_publication',
            '_attribution', '_origin', '_plan', '_operation', '_run', '_log'];
        foreach ($tables as $suffix) {
            if ($DB->record_exists('local_multipicker' . $suffix, [])) {
                throw new \coding_exception('MultiPicker already contains data; the legacy import cannot merge installations.');
            }
            if (!$dbman->table_exists(new \xmldb_table('local_coursebridge' . $suffix))) {
                throw new \coding_exception('Upgrade CourseBridge before installing MultiPicker: missing legacy table ' . $suffix);
            }
        }
        $transaction = $DB->start_delegated_transaction();
        foreach ($tables as $suffix) {
            $records = $DB->get_recordset('local_coursebridge' . $suffix, [], 'id');
            foreach ($records as $record) {
                $DB->import_record('local_multipicker' . $suffix, $record);
            }
            $records->close();
        }
        foreach ($DB->get_records('config_plugins', ['plugin' => 'local_coursebridge']) as $setting) {
            if ($setting->name !== 'version') {
                set_config($setting->name, $setting->value, 'local_multipicker');
            }
        }
        foreach (['local/coursebridge:', 'repository/coursebridge:'] as $prefix) {
            $records = $DB->get_records_select(
                'role_capabilities',
                $DB->sql_like('capability', ':prefix'),
                ['prefix' => $DB->sql_like_escape($prefix) . '%']
            );
            foreach ($records as $record) {
                $record->capability = str_replace('/coursebridge:', '/multipicker:', $record->capability);
                $existing = $DB->get_record('role_capabilities', ['roleid' => $record->roleid,
                    'contextid' => $record->contextid, 'capability' => $record->capability]);
                if ($existing) {
                    $record->id = $existing->id;
                    $DB->update_record('role_capabilities', $record);
                } else {
                    unset($record->id);
                    $DB->insert_record('role_capabilities', $record);
                }
            }
        }
        foreach ($DB->get_records('task_adhoc', ['component' => 'local_coursebridge']) as $task) {
            $task->component = 'local_multipicker';
            $task->classname = str_replace('local_coursebridge\\', 'local_multipicker\\', $task->classname);
            $DB->update_record('task_adhoc', $task);
        }
        // Old scheduled tasks must not run against the retained rollback tables.
        $DB->set_field('task_scheduled', 'disabled', 1, ['component' => 'local_coursebridge']);
        set_config('legacy_version', get_config('local_coursebridge', 'version'), 'local_multipicker');
        unset_config('version', 'local_coursebridge');
        set_config('legacy_imported', time(), 'local_multipicker');
        $transaction->allow_commit();
        foreach ($tables as $suffix) {
            $dbman->reset_sequence(new \xmldb_table('local_multipicker' . $suffix));
        }
    }
}
