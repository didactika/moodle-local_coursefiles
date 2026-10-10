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
 * Preserve CourseBridge installations when adopting the CourseFiles component names.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\local\upgrade;

/**

 * Imports legacy data once; legacy tables remain available for a controlled rollback.

 */
final class legacy_import {
    /**
     * Remove retired scheduled registrations and preserve supported queued work.
     * Disabled registrations also get instantiated by cron, so disabling is insufficient.
     */
    public static function repair_tasks(): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('task_scheduled', ['component' => 'local_coursebridge']);
        foreach ($DB->get_records('task_adhoc', ['component' => 'local_coursebridge']) as $task) {
            $classname = str_replace('local_coursebridge\\', 'local_coursefiles\\', $task->classname);
            if (!is_subclass_of($classname, \core\task\adhoc_task::class)) {
                throw new \coding_exception('Unsupported legacy queued task: ' . $task->classname);
            }
            $task->component = 'local_coursefiles';
            $task->classname = $classname;
            if (property_exists($task, 'identityhash')) {
                // Moodle 5.3 rebuilds nullable legacy identities when deduplicating queued work.
                $task->identityhash = null;
            }
            $DB->update_record('task_adhoc', $task);
        }
        $transaction->allow_commit();
    }

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
        $targets = ['_coursepolicy' => '_policy', '_publication' => '_publish', '_attribution' => '_author'];
        $sources = [];
        foreach ($tables as $suffix) {
            if ($DB->record_exists('local_coursefiles' . ($targets[$suffix] ?? $suffix), [])) {
                throw new \coding_exception('CourseFiles already contains data; the legacy import cannot merge installations.');
            }
            $source = 'local_coursebridge' . $suffix;
            $shortsource = 'local_coursebridge' . ($targets[$suffix] ?? $suffix);
            if (
                !$dbman->table_exists(new \xmldb_table($source))
                    && $dbman->table_exists(new \xmldb_table($shortsource))
            ) {
                $source = $shortsource;
            }
            if (!$dbman->table_exists(new \xmldb_table($source))) {
                throw new \coding_exception('Upgrade CourseBridge before installing CourseFiles: missing legacy table ' . $suffix);
            }
            $sources[$suffix] = $source;
        }
        $transaction = $DB->start_delegated_transaction();
        foreach ($tables as $suffix) {
            $records = $DB->get_recordset($sources[$suffix], [], 'id');
            foreach ($records as $record) {
                $DB->import_record('local_coursefiles' . ($targets[$suffix] ?? $suffix), $record);
            }
            $records->close();
        }
        foreach ($DB->get_records('config_plugins', ['plugin' => 'local_coursebridge']) as $setting) {
            if ($setting->name !== 'version') {
                set_config($setting->name, $setting->value, 'local_coursefiles');
            }
        }
        foreach (['local/coursebridge:', 'repository/coursebridge:'] as $prefix) {
            $records = $DB->get_records_select(
                'role_capabilities',
                $DB->sql_like('capability', ':prefix'),
                ['prefix' => $DB->sql_like_escape($prefix) . '%']
            );
            foreach ($records as $record) {
                $record->capability = str_replace('/coursebridge:', '/coursefiles:', $record->capability);
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
        // Old scheduled tasks must not run against the retained rollback tables.
        self::repair_tasks();
        set_config('legacy_version', get_config('local_coursebridge', 'version'), 'local_coursefiles');
        unset_config('version', 'local_coursebridge');
        set_config('legacy_imported', time(), 'local_coursefiles');
        $transaction->allow_commit();
        foreach ($tables as $suffix) {
            $dbman->reset_sequence(new \xmldb_table('local_coursefiles' . ($targets[$suffix] ?? $suffix)));
        }
    }
}
