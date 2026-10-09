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
 * MultiPicker database upgrades.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Preserve action records when upgrading the log schema.
 *
 * @param int $oldversion Installed version.
 * @return bool Upgrade completed.
 */
function xmldb_local_multipicker_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2026100801) {
        $oldtable = new xmldb_table('local_multipicker_audit');
        $newtable = new xmldb_table('local_multipicker_log');
        if ($dbman->table_exists($oldtable)) {
            if ($dbman->table_exists($newtable)) {
                throw new coding_exception('Both MultiPicker history tables exist; resolve the conflict before upgrading.');
            }
            $dbman->rename_table($oldtable, 'local_multipicker_log');
        }
        if (!$dbman->table_exists($newtable)) {
            throw new coding_exception('MultiPicker log table is missing.');
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
        upgrade_plugin_savepoint(true, 2026100801, 'local', 'multipicker');
    }
    if ($oldversion < 2026100802) {
        $table = new xmldb_table('local_multipicker_attribution');
        $field = new xmldb_field('parentuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_default($table, $field);
        upgrade_plugin_savepoint(true, 2026100802, 'local', 'multipicker');
    }
    if ($oldversion < 2026100902) {
        $file = new xmldb_file(__DIR__ . '/install.xml');
        if (!$file->loadXMLStructure()) {
            throw new coding_exception('Unable to load the MultiPicker XMLDB schema.');
        }
        $table = $file->getStructure()->getTable('local_multipicker_value');
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026100902, 'local', 'multipicker');
    }
    if ($oldversion < 2026100903) {
        $table = new xmldb_table('local_multipicker_operation');
        $field = new xmldb_field('included', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'decision');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $index = new xmldb_index('planid-included-state', XMLDB_INDEX_NOTUNIQUE, ['planid', 'included', 'state']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_plugin_savepoint(true, 2026100903, 'local', 'multipicker');
    }
    return true;
}
