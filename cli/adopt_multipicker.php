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
 * Adopt an existing MultiPicker installation without copying course file binaries.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognised] = cli_get_params(['help' => false, 'execute' => false], ['h' => 'help']);
if ($unrecognised) {
    cli_error('Unknown options: ' . implode(', ', $unrecognised));
}
if ($options['help']) {
    echo "Adopt MultiPicker metadata as CourseFiles. Back up the database first.\n";
    echo "Run without --execute to inspect; run with --execute before admin/cli/upgrade.php.\n";
    exit(0);
}
$dbman = $DB->get_manager();
if (
    $DB->record_exists('repository', ['type' => 'multipicker']) &&
        $DB->record_exists('repository', ['type' => 'coursefiles'])
) {
    cli_error('Both repository types exist; resolve their instances before adoption.');
}
$schema = new xmldb_file(__DIR__ . '/../db/install.xml');
if (!$schema->loadXMLStructure()) {
    cli_error('Unable to read the CourseFiles schema.');
}
$moves = [];
foreach ($schema->getStructure()->getTables() as $table) {
    $new = $table->getName();
    $old = str_replace('local_coursefiles', 'local_multipicker', $new);
    $old = str_replace(
        ['local_multipicker_policy', 'local_multipicker_publish', 'local_multipicker_author'],
        ['local_multipicker_coursepolicy', 'local_multipicker_publication', 'local_multipicker_attribution'],
        $old
    );
    $sourceexists = $dbman->table_exists(new xmldb_table($old));
    $targetexists = $dbman->table_exists(new xmldb_table($new));
    if ($sourceexists && $targetexists) {
        cli_error('Both tables exist; no data has been changed: ' . $old . ' / ' . $new);
    }
    if (!$sourceexists && !$targetexists) {
        cli_error('Missing source table: ' . $old . '. Upgrade the old installation first.');
    }
    if ($sourceexists) {
        $moves[$old] = $new;
    }
}
foreach ($moves as $old => $new) {
    echo $old . ' -> ' . $new . "\n";
}
if (!$options['execute']) {
    echo "Inspection only. No database changes made.\n";
    exit(0);
}
// Preflight precedes DDL. Interrupted runs resume from tables already renamed.
foreach ($moves as $old => $new) {
    $dbman->rename_table(new xmldb_table($old), $new);
}
$transaction = $DB->start_delegated_transaction();
foreach (['local', 'repository'] as $type) {
    $old = $type . '_multipicker';
    $new = $type . '_coursefiles';
    foreach ($DB->get_records('config_plugins', ['plugin' => $old]) as $setting) {
        if (!$DB->record_exists('config_plugins', ['plugin' => $new, 'name' => $setting->name])) {
            $setting->plugin = $new;
            $DB->update_record('config_plugins', $setting);
        }
    }
    $DB->delete_records('config_plugins', ['plugin' => $old]);
    foreach (['task_scheduled', 'task_adhoc'] as $name) {
        foreach ($DB->get_records($name, ['component' => $old]) as $task) {
            $task->component = $new;
            $task->classname = str_replace($old . '\\', $new . '\\', $task->classname);
            if ($name === 'task_adhoc' && property_exists($task, 'identityhash')) {
                $task->identityhash = null;
            }
            $DB->update_record($name, $task);
        }
    }
    $oldprefix = $type . '/multipicker:';
    foreach (
        $DB->get_records_select(
            'role_capabilities',
            $DB->sql_like('capability', ':prefix'),
            ['prefix' => $DB->sql_like_escape($oldprefix) . '%']
        ) as $grant
    ) {
        $grant->capability = str_replace($oldprefix, $type . '/coursefiles:', $grant->capability);
        if (
            $DB->record_exists('role_capabilities', ['roleid' => $grant->roleid, 'contextid' => $grant->contextid,
            'capability' => $grant->capability])
        ) {
            $DB->delete_records('role_capabilities', ['id' => $grant->id]);
        } else {
            $DB->update_record('role_capabilities', $grant);
        }
    }
}
$DB->set_field('repository', 'type', 'coursefiles', ['type' => 'multipicker']);
// File-source declarations remain unchanged; attribution is resolved through the retained origin rows.
$transaction->allow_commit();
purge_all_caches();
echo "Adoption complete. Remove the retired plugin directories, then run Moodle's CLI upgrade.\n";
