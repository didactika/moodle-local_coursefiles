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
 * Relationship and policy integration tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank;

use local_filebank\local\profile\profile_config;
use local_filebank\local\profile\profiles;
use local_filebank\local\catalog\indexer;
use local_filebank\local\catalog\catalog;
use local_filebank\local\migration\copy_decision;
use local_filebank\local\migration\migration_planner;
use local_filebank\local\migration\migration_executor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/profile_config_fixture.php');

/**
 * Limits regression contracts.
 * @covers \local_filebank\local\profile\profile_config
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\migration\copy_decision
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\migration_executor
 */
final class profile_config_limits_test extends profile_config_fixture {
    /**
     * A changed destination layout cannot receive a reviewed copy.
     */
    public function test_changed_destination_is_blocked(): void {

        global $CFG, $DB;
        [$profileid, , $target, , $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $DB->set_field('folder', 'name', 'changed', ['id' => $tfolder->id]);
        rebuild_course_cache($target->id, true);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('blocked', $DB->get_field('local_filebank_operation', 'state', ['planid' => $plan->id]));
        $this->assertCount(
            0,
            get_file_storage()->get_area_files(
                \context_module::instance($tfolder->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            )
        );
    }
}
