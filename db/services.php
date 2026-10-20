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
 * External functions of the plugin.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_filebank_get_import_status' => [
        'classname' => 'local_filebank\external\get_import_status',
        'methodname' => 'execute',
        'description' => 'Get the files that the import of an owning editor left in its draft.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:reuse',
    ],
    'local_filebank_migration_list' => [
        'classname' => 'local_filebank\external\migration\list_migrations',
        'methodname' => 'execute',
        'description' => 'Read a page of the migrations of the site.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_options' => [
        'classname' => 'local_filebank\external\migration\get_options',
        'methodname' => 'execute',
        'description' => 'Read what a new migration can be asked to do.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_check' => [
        'classname' => 'local_filebank\external\migration\check_migration',
        'methodname' => 'execute',
        'description' => 'Count what a migration with these parameters would do, without creating it.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_create' => [
        'classname' => 'local_filebank\external\migration\create_migration',
        'methodname' => 'execute',
        'description' => 'Work a migration out and store it for review.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_get' => [
        'classname' => 'local_filebank\external\migration\get_migration',
        'methodname' => 'execute',
        'description' => 'Read one migration with its numbers, parameters and what waits for a decision.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_decisions' => [
        'classname' => 'local_filebank\external\migration\get_decisions',
        'methodname' => 'execute',
        'description' => 'Read a page of the files in conflict or of the issues of one nature.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_files' => [
        'classname' => 'local_filebank\external\migration\get_files',
        'methodname' => 'execute',
        'description' => 'Read a page of the files of a migration.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_folders' => [
        'classname' => 'local_filebank\external\migration\get_folders',
        'methodname' => 'execute',
        'description' => 'Read a page of the folders of a migration.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_resolve' => [
        'classname' => 'local_filebank\external\migration\resolve_decisions',
        'methodname' => 'execute',
        'description' => 'Settle what waits for a decision inside a migration.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_reconfigure' => [
        'classname' => 'local_filebank\external\migration\reconfigure_migration',
        'methodname' => 'execute',
        'description' => 'Change the parameters of a migration that has not run.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_select' => [
        'classname' => 'local_filebank\external\migration\select_files',
        'methodname' => 'execute',
        'description' => 'Choose which files are part of a migration.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
    'local_filebank_migration_execute' => [
        'classname' => 'local_filebank\external\migration\execute_migration',
        'methodname' => 'execute',
        'description' => 'Queue the run of a reviewed migration.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:executemigration',
    ],
    'local_filebank_migration_cancel' => [
        'classname' => 'local_filebank\external\migration\cancel_run',
        'methodname' => 'execute',
        'description' => 'Stop the run of a migration.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:executemigration',
    ],
    'local_filebank_migration_delete' => [
        'classname' => 'local_filebank\external\migration\delete_migrations',
        'methodname' => 'execute',
        'description' => 'Delete migrations from the history.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/filebank:planmigration',
    ],
];
