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
 * CourseBridge database upgrades.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Preserve action records when upgrading the log schema.
 *
 * @param int $oldversion Installed version.
 * @return bool Upgrade completed.
 */
function xmldb_local_coursebridge_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2026100801) {
        $oldtable = new xmldb_table('local_coursebridge_audit');
        $newtable = new xmldb_table('local_coursebridge_log');
        if ($dbman->table_exists($oldtable)) {
            if ($dbman->table_exists($newtable)) {
                throw new coding_exception('Both CourseBridge history tables exist; resolve the conflict before upgrading.');
            }
            $dbman->rename_table($oldtable, 'local_coursebridge_log');
        }
        if (!$dbman->table_exists($newtable)) {
            throw new coding_exception('CourseBridge log table is missing.');
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
        upgrade_plugin_savepoint(true, 2026100801, 'local', 'coursebridge');
    }
    if ($oldversion < 2026100802) {
        $table = new xmldb_table('local_coursebridge_attribution');
        $field = new xmldb_field('parentuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_default($table, $field);
        upgrade_plugin_savepoint(true, 2026100802, 'local', 'coursebridge');
    }
    return true;
}
