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
 * Legacy component rename preservation.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles;

use local_coursefiles\local\upgrade\legacy_import;

/**

 * Verifies IDs, history and settings survive the component rename.

 * @covers \local_coursefiles\local\upgrade\legacy_import
 */
final class legacy_import_test extends \advanced_testcase {
    /**
     * Import populated legacy tables and reset sequences for subsequent writes.
     */
    public function test_legacy_import_preserves_ids_and_settings(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $manager = $DB->get_manager();
        $file = new \xmldb_file(__DIR__ . '/../db/install.xml');
        $file->loadXMLStructure();
        $tables = [];
        foreach ($file->getStructure()->getTables() as $table) {
            if ($table->getName() === 'local_coursefiles_value') {
                continue;
            }
            $table->setName(str_replace('local_coursefiles', 'local_coursebridge', $table->getName()));
            $manager->create_table($table);
            $tables[] = $table;
        }
        try {
            $DB->import_record('local_coursebridge', (object)['id' => 42, 'uuid' => \core\uuid::generate(),
                'name' => 'Retained profile', 'activeid' => 0, 'enabled' => 1]);
            $DB->import_record('local_coursebridge_log', (object)['id' => 64, 'userid' => 2, 'action' => 'profile_activated',
                'entityuuid' => '', 'details' => '{"revision":1}', 'timecreated' => time()]);
            set_config('version', 2026100901, 'local_coursebridge');
            set_config('author_policy', 'editable_with_provenance', 'local_coursebridge');
            $roleid = create_role('Legacy restricted role', 'legacyrestrictedrole', '');
            $DB->insert_record('role_capabilities', (object)[
                'roleid' => $roleid, 'contextid' => \context_system::instance()->id,
                'capability' => 'local/coursebridge:reuse', 'permission' => CAP_PROHIBIT,
                'timemodified' => time(), 'modifierid' => 2,
            ]);
            legacy_import::execute();
            $this->assertSame('Retained profile', $DB->get_field('local_coursefiles', 'name', ['id' => 42]));
            $this->assertSame('{"revision":1}', $DB->get_field('local_coursefiles_log', 'details', ['id' => 64]));
            $this->assertSame('editable_with_provenance', get_config('local_coursefiles', 'author_policy'));
            $this->assertFalse(get_config('local_coursebridge', 'version'));
            $this->assertEquals(CAP_PROHIBIT, $DB->get_field('role_capabilities', 'permission', [
                'roleid' => $roleid, 'capability' => 'local/coursefiles:reuse',
                'contextid' => \context_system::instance()->id,
            ]));
            $id = $DB->insert_record('local_coursefiles', (object)['uuid' => \core\uuid::generate(),
                'name' => 'New profile', 'activeid' => 0, 'enabled' => 1]);
            $this->assertGreaterThan(42, $id);
            $this->expectException(\coding_exception::class);
            legacy_import::execute();
        } finally {
            foreach (array_reverse($tables) as $table) {
                $manager->drop_table($table);
            }
        }
    }
}
