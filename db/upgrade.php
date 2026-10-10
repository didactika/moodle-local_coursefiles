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
 * FileBank database upgrades.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Preserve action records when upgrading the log schema.
 *
 * @param int $oldversion Installed version.
 * @return bool Upgrade completed.
 */
function xmldb_local_filebank_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2026100801) {
        $oldtable = new xmldb_table('local_filebank_audit');
        $newtable = new xmldb_table('local_filebank_log');
        if ($dbman->table_exists($oldtable)) {
            if ($dbman->table_exists($newtable)) {
                throw new coding_exception('Both FileBank history tables exist; resolve the conflict before upgrading.');
            }
            $dbman->rename_table($oldtable, 'local_filebank_log');
        }
        if (!$dbman->table_exists($newtable)) {
            throw new coding_exception('FileBank log table is missing.');
        }
        foreach (
            [
            new xmldb_index('actionid', XMLDB_INDEX_NOTUNIQUE, ['action', 'id']),
            new xmldb_index('entityid', XMLDB_INDEX_NOTUNIQUE, ['entityuuid', 'id']),
            ] as $index
        ) {
            if (!$dbman->index_exists($newtable, $index)) {
                $dbman->add_index($newtable, $index);
            }
        }
        upgrade_plugin_savepoint(true, 2026100801, 'local', 'filebank');
    }
    if ($oldversion < 2026100802) {
        $table = new xmldb_table('local_filebank_author');
        $field = new xmldb_field('parentuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_default($table, $field);
        upgrade_plugin_savepoint(true, 2026100802, 'local', 'filebank');
    }
    if ($oldversion < 2026100902) {
        $file = new xmldb_file(__DIR__ . '/install.xml');
        if (!$file->loadXMLStructure()) {
            throw new coding_exception('Unable to load the FileBank XMLDB schema.');
        }
        $table = $file->getStructure()->getTable('local_filebank_value');
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026100902, 'local', 'filebank');
    }
    if ($oldversion < 2026100903) {
        $table = new xmldb_table('local_filebank_operation');
        $field = new xmldb_field('included', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'decision');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $index = new xmldb_index('planid-included-state', XMLDB_INDEX_NOTUNIQUE, ['planid', 'included', 'state']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_plugin_savepoint(true, 2026100903, 'local', 'filebank');
    }
    if ($oldversion < 2026100904) {
        $file = new xmldb_file(__DIR__ . '/install.xml');
        if (!$file->loadXMLStructure()) {
            throw new coding_exception('Unable to load the FileBank XMLDB schema.');
        }
        $targettable = $file->getStructure()->getTable('local_filebank_target');
        if (!$dbman->table_exists($targettable)) {
            $dbman->create_table($targettable);
        }
        $table = new xmldb_table('local_filebank');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table('local_filebank_operation');
        foreach (['sourcecourseid', 'targetcourseid', 'sourcecmid'] as $name) {
            $field = new xmldb_field($name, XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        $records = $DB->get_recordset_select('local_filebank_operation', 'sourcecourseid = 0');
        foreach ($records as $operation) {
            $snapshot = json_decode($operation->snapshot, true);
            $operation->sourcecourseid = $snapshot['sourcecourse'] ?? 0;
            $operation->targetcourseid = $snapshot['target']['courseid'] ?? 0;
            $contextid = $DB->get_field('files', 'contextid', ['pathnamehash' => $operation->sourcehash]) ?: 0;
            $context = $contextid ? context::instance_by_id($contextid, IGNORE_MISSING) : null;
            $operation->sourcecmid = $context && $context->contextlevel === CONTEXT_MODULE ? $context->instanceid : 0;
            $DB->update_record('local_filebank_operation', $operation);
        }
        $records->close();
        foreach (
            [
            'local_filebank_plan' => ['timecreated-id', ['timecreated', 'id']],
            'local_filebank_run' => ['planid-state-time', ['planid', 'state', 'timemodified']],
            'local_filebank_operation' => ['planid-course-folder', ['planid', 'sourcecourseid', 'sourcecmid']],
            ] as $name => [$indexname, $fields]
        ) {
            $table = new xmldb_table($name);
            $index = new xmldb_index($indexname, XMLDB_INDEX_NOTUNIQUE, $fields);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }
        upgrade_plugin_savepoint(true, 2026100904, 'local', 'filebank');
    }
    if ($oldversion < 2026100905) {
        $file = new xmldb_file(__DIR__ . '/install.xml');
        if (!$file->loadXMLStructure()) {
            throw new coding_exception('Unable to load the FileBank XMLDB schema.');
        }
        $table = $file->getStructure()->getTable('local_filebank_target');
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026100905, 'local', 'filebank');
    }
    if ($oldversion < 2026101000) {
        $table = new xmldb_table('local_filebank_revision');
        $index = new xmldb_index('timecreated-id', XMLDB_INDEX_NOTUNIQUE, ['timecreated', 'id']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_plugin_savepoint(true, 2026101000, 'local', 'filebank');
    }
    if ($oldversion < 2026101001) {
        \local_filebank\local\upgrade\legacy_import::repair_tasks();
        upgrade_plugin_savepoint(true, 2026101001, 'local', 'filebank');
    }
    if ($oldversion < 2026101002) {
        upgrade_plugin_savepoint(true, 2026101002, 'local', 'filebank');
    }
    if ($oldversion < 2026101003) {
        upgrade_plugin_savepoint(true, 2026101003, 'local', 'filebank');
    }
    if ($oldversion < 2026101004) {
        upgrade_plugin_savepoint(true, 2026101004, 'local', 'filebank');
    }
    if ($oldversion < 2026101100) {
        upgrade_plugin_savepoint(true, 2026101100, 'local', 'filebank');
    }
    if ($oldversion < 2026101101) {
        foreach (['_coursepolicy' => '_policy', '_publication' => '_publish', '_attribution' => '_author'] as $old => $new) {
            $source = new xmldb_table('local_filebank' . $old);
            $target = new xmldb_table('local_filebank' . $new);
            if ($dbman->table_exists($source)) {
                if ($dbman->table_exists($target)) {
                    throw new coding_exception('Both FileBank table names exist: ' . $old . ' / ' . $new);
                }
                $dbman->rename_table($source, 'local_filebank' . $new);
            }
        }
        upgrade_plugin_savepoint(true, 2026101101, 'local', 'filebank');
    }
    return true;
}
