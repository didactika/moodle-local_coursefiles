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
 * Existing installation upgrade tests.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker;

/**
 * Confirms that schema and permission changes preserve existing records.
 */
final class upgrade_test extends \advanced_testcase {
    /**
     * The real XMLDB parser loads the schema without normalizing an empty CHAR default.
     */
    public function test_install_schema_loads_without_invalid_char_defaults(): void {
        $this->resetAfterTest();
        $file = new \xmldb_file(__DIR__ . '/../db/install.xml');
        $this->assertTrue($file->loadXMLStructure());
        $structure = $file->getStructure();
        $field = $structure->getTable('local_multipicker_attribution')->getField('parentuuid');
        $this->assertNull($field->getDefault());
        $this->assertTrue($field->getNotNull());
    }

    /**
     * Correcting the parent default preserves root and child attribution values.
     */
    public function test_parent_default_upgrade_preserves_lineage(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        require_once($CFG->libdir . '/upgradelib.php');
        require_once(__DIR__ . '/../db/upgrade.php');
        $parentuuid = \core\uuid::generate();
        $ids = [];
        foreach (['' => $parentuuid, $parentuuid => \core\uuid::generate()] as $parent => $uuid) {
            $ids[] = $DB->insert_record('local_multipicker_attribution', (object)[
                'uuid' => $uuid, 'parentuuid' => $parent, 'sourcehash' => str_repeat('a', 40),
                'contenthash' => str_repeat('b', 40), 'author' => 'Original author', 'license' => 'cc',
                'locked' => 0, 'userid' => 2, 'timecreated' => 1700000000,
            ]);
        }
        $before = $DB->get_records_list('local_multipicker_attribution', 'id', $ids, 'id');
        set_config('version', 2026100801, 'local_multipicker');
        $this->assertTrue(\xmldb_local_multipicker_upgrade(2026100801));
        $this->assertEquals($before, $DB->get_records_list('local_multipicker_attribution', 'id', $ids, 'id'));
        $this->assertSame('2026100903', get_config('local_multipicker', 'version'));
    }

    /**
     * A conflicting installation stops without deleting either history.
     */
    public function test_conflicting_tables_stop_the_upgrade(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->libdir . '/upgradelib.php');
        require_once(__DIR__ . '/../db/upgrade.php');
        $dbman = $DB->get_manager();
        $legacy = new \xmldb_table('local_multipicker_audit');
        $legacy->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $legacy->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $dbman->create_table($legacy);
        set_config('version', 2026100800, 'local_multipicker');
        try {
            try {
                \xmldb_local_multipicker_upgrade(2026100800);
                $this->fail('Conflicting history tables must stop the upgrade.');
            } catch (\coding_exception $exception) {
                $this->assertStringContainsString('Both MultiPicker history tables exist', $exception->getMessage());
            }
            $this->assertTrue($dbman->table_exists($legacy));
            $this->assertTrue($dbman->table_exists('local_multipicker_log'));
            $this->assertSame('2026100800', get_config('local_multipicker', 'version'));
        } finally {
            $dbman->drop_table($legacy);
        }
    }

    /**
     * Upgrade a legacy table with a real record and preserve its ID and contents.
     */
    public function test_legacy_table_is_renamed_without_losing_records(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/upgradelib.php');
        require_once(__DIR__ . '/../db/upgrade.php');
        $dbman = $DB->get_manager();
        $table = new \xmldb_table('local_multipicker_log');
        $record = (object)['userid' => 2, 'action' => 'migration_batch', 'entityuuid' => 'existing-reference',
            'details' => '{"state":"completed"}', 'timecreated' => 1700000000];
        $record->id = $DB->insert_record('local_multipicker_log', $record);
        foreach (
            [
            new \xmldb_index('actionid', XMLDB_INDEX_NOTUNIQUE, ['action', 'id']),
            new \xmldb_index('entityid', XMLDB_INDEX_NOTUNIQUE, ['entityuuid', 'id']),
            ] as $index
        ) {
            $dbman->drop_index($table, $index);
        }
        $dbman->rename_table($table, 'local_multipicker_audit');
        set_config('version', 2026100800, 'local_multipicker');
        try {
            $this->assertTrue(\xmldb_local_multipicker_upgrade(2026100800));
            $this->assertEquals($record, $DB->get_record('local_multipicker_log', ['id' => $record->id], '*', MUST_EXIST));
            $this->assertFalse($dbman->table_exists('local_multipicker_audit'));
            $this->assertTrue($dbman->index_exists(
                $table,
                new \xmldb_index('actionid', XMLDB_INDEX_NOTUNIQUE, ['action', 'id'])
            ));
            $this->assertTrue($dbman->index_exists(
                $table,
                new \xmldb_index('entityid', XMLDB_INDEX_NOTUNIQUE, ['entityuuid', 'id'])
            ));
            $this->assertSame('2026100903', get_config('local_multipicker', 'version'));
            $this->assertTrue(\xmldb_local_multipicker_upgrade(2026100903));
        } finally {
            if ($dbman->table_exists('local_multipicker_audit') && !$dbman->table_exists($table)) {
                $dbman->rename_table(new \xmldb_table('local_multipicker_audit'), 'local_multipicker_log');
            }
        }
    }

    /**
     * A completed rename can resume before its savepoint without duplicating records.
     */
    public function test_upgrade_resumes_after_table_rename(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->libdir . '/upgradelib.php');
        require_once(__DIR__ . '/../db/upgrade.php');
        set_config('version', 2026100800, 'local_multipicker');
        $this->assertTrue(\xmldb_local_multipicker_upgrade(2026100800));
        $this->assertSame('2026100903', get_config('local_multipicker', 'version'));
        $this->assertTrue($DB->get_manager()->table_exists('local_multipicker_log'));
    }

    /**
     * Moodle clones explicit grants and prohibitions before removing the old capability.
     */
    public function test_capability_rename_preserves_role_permissions(): void {
        global $DB;
        $this->resetAfterTest();
        $system = \context_system::instance();
        $old = 'local/multipicker:viewaudit';
        $new = 'local/multipicker:viewlog';
        $definition = $DB->get_record('capabilities', ['name' => $new], '*', MUST_EXIST);
        $DB->delete_records('role_capabilities', ['capability' => $new]);
        $DB->delete_records('capabilities', ['name' => $new]);
        unset($definition->id);
        $definition->name = $old;
        $DB->insert_record('capabilities', $definition);
        $allowed = $this->getDataGenerator()->create_role();
        $prohibited = $this->getDataGenerator()->create_role();
        assign_capability($old, CAP_ALLOW, $allowed, $system->id);
        assign_capability($old, CAP_PROHIBIT, $prohibited, $system->id);
        update_capabilities('local_multipicker');
        $this->assertSame(CAP_ALLOW, (int)$DB->get_field(
            'role_capabilities',
            'permission',
            ['roleid' => $allowed, 'contextid' => $system->id, 'capability' => $new]
        ));
        $this->assertSame(CAP_PROHIBIT, (int)$DB->get_field(
            'role_capabilities',
            'permission',
            ['roleid' => $prohibited, 'contextid' => $system->id, 'capability' => $new]
        ));
        $this->assertFalse($DB->record_exists('capabilities', ['name' => $old]));
        $this->assertFalse($DB->record_exists('role_capabilities', ['capability' => $old]));
    }
}
