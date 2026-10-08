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
 * Action log permissions, pagination and privacy tests.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge;

use local_coursebridge\local\log;
use local_coursebridge\privacy\provider;

/**
 * Exercises actual stored actions rather than synthetic UI counts.
 */
final class log_test extends \advanced_testcase {
    /**
     * A dedicated reader needs no profile or migration editing permission.
     */
    public function test_reader_role_can_read_without_editing_permissions(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        log::record('profile_activated', 'profile-reference', []);
        $reader = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        $system = \context_system::instance();
        assign_capability('local/coursebridge:viewlog', CAP_ALLOW, $role, $system->id);
        role_assign($role, $reader->id, $system->id);
        $this->setUser($reader);
        $this->assertCount(1, log::page()['records']);
        $this->assertFalse(has_capability('local/coursebridge:manageprofiles', $system));
        $this->assertFalse(has_capability('local/coursebridge:planmigration', $system));
    }

    /**
     * Readers without the site capability cannot retrieve personal action records.
     */
    public function test_read_requires_site_permission(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        log::page();
    }

    /**
     * An inserted action does not repeat or displace records on subsequent cursor pages.
     */
    public function test_filters_and_cursor_survive_new_records(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        for ($i = 0; $i < 4; $i++) {
            log::record('migration_batch', 'plan-reference', ['batch' => $i]);
        }
        log::record('profile_activated', 'plan-reference', []);
        log::record('migration_batch', 'other-reference', []);
        $first = log::page('migration_batch', 'plan-reference', 0, 2);
        $this->assertCount(2, $first['records']);
        $this->assertGreaterThan(0, $first['nextbefore']);
        log::record('migration_batch', 'plan-reference', []);
        $second = log::page('migration_batch', 'plan-reference', $first['nextbefore'], 2);
        $this->assertCount(2, $second['records']);
        $this->assertSame(0, $second['nextbefore']);
        $this->assertEmpty(array_intersect(array_column($first['records'], 'id'), array_column($second['records'], 'id')));
        foreach (array_merge($first['records'], $second['records']) as $record) {
            $this->assertSame('migration_batch', $record->action);
            $this->assertSame('plan-reference', $record->entityuuid);
        }
    }

    /**
     * Approved deletion removes only the actor's log, including its details.
     */
    public function test_privacy_deletes_only_approved_actor(): void {
        global $DB;
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($actor);
        log::record('migration_batch', 'plan-reference', ['author' => 'Declared author']);
        $this->setUser($other);
        log::record('migration_batch', 'plan-reference', []);
        $system = \context_system::instance();
        $contexts = provider::get_contexts_for_userid($actor->id);
        $this->assertEquals([$system->id], $contexts->get_contextids());
        provider::delete_data_for_user(new \core_privacy\local\request\approved_contextlist(
            $actor,
            'local_coursebridge',
            [$system->id]
        ));
        $this->assertFalse($DB->record_exists('local_coursebridge_log', ['userid' => $actor->id]));
        $this->assertTrue($DB->record_exists('local_coursebridge_log', ['userid' => $other->id]));
        $this->assertEmpty(provider::get_contexts_for_userid($actor->id)->get_contextids());
    }
}
