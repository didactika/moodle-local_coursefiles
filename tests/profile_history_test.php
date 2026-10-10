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
 * Version round trips, retention, plan lifecycle and native capacity.
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker;

use local_multipicker\local\profile\profiles;
use local_multipicker\local\profile\profile_history;
use local_multipicker\local\profile\profile_bundle;
use local_multipicker\local\migration\migration_planner;
use local_multipicker\local\migration\migration_executor;
use local_multipicker\local\migration\plan_history;
use local_multipicker\local\selection\selection_limits;

/**
 * Exercises public service contracts with real Moodle files and profiles.
 * @covers \local_multipicker\local\profile\profile_history
 * @covers \local_multipicker\local\profile\profile_bundle
 * @covers \local_multipicker\local\migration\plan_history
 * @covers \local_multipicker\local\selection\selection_limits
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\profile\profile_history::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\profile\profile_bundle::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\migration\plan_history::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\selection\selection_limits::class)]
final class profile_history_test extends \advanced_testcase {
    /**
     * Restore one identity and transport all retained versions with their dates.
     */
    public function test_restoration_comparison_and_history_round_trip(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $config = json_decode(file_get_contents(__DIR__ . '/../examples/institutional-profile.json'), true);
        $id = profiles::save(json_encode($config));
        $first = $DB->get_field('local_multipicker', 'activeid', ['id' => $id]);
        $original = $DB->get_record('local_multipicker_revision', ['id' => $first]);
        $config['name'] = 'Changed profile';
        profiles::save(json_encode($config), $id);
        $this->assertSame($id, profile_history::restore($id, $first));
        $this->assertSame(1, $DB->count_records('local_multipicker'));
        $this->assertSame(2, $DB->count_records('local_multipicker_revision', ['profileid' => $id]));
        $this->assertEquals($first, $DB->get_field('local_multipicker', 'activeid', ['id' => $id]));
        $this->assertNotSame('Changed profile', $DB->get_field('local_multipicker', 'name', ['id' => $id]));
        $this->assertEquals($original, $DB->get_record('local_multipicker_revision', ['id' => $first]));
        profile_history::restore($id, $first);
        $this->assertSame(2, $DB->count_records('local_multipicker_revision', ['profileid' => $id]));
        $bundle = profile_bundle::export([$id]);
        $document = json_decode($bundle, true);
        $this->assertSame(2, $document['version']);
        $imported = profile_bundle::import($bundle);
        $copy = json_decode(profile_bundle::export($imported), true);
        $this->assertSame($document['profiles'], $copy['profiles']);
        $diff = array_filter(
            profile_history::compare(['name' => '<b>Old</b>', 'removed' => 1], ['name' => 'New']),
            static fn($row) => $row['changed']
        );
        $this->assertCount(2, $diff);
        $other = profiles::save(json_encode($config));
        $this->expectException(\dml_missing_record_exception::class);
        profile_history::restore($other, $first);
    }

    /**
     * Live profile actions return only affected state and keep one profile/version identity.
     */
    public function test_profile_state_fragment_checks_session_and_preserves_versions(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once(__DIR__ . '/../lib.php');
        $config = file_get_contents(__DIR__ . '/../examples/institutional-profile.json');
        $id = profiles::save($config);
        $args = ['context' => \context_system::instance(),
            'state' => json_encode(['ids' => [$id], 'action' => 'disable', 'sesskey' => sesskey()])];
        $html = \local_multipicker_output_fragment_profiles($args);
        $this->assertStringContainsString('data-multipicker-profile-state', $html);
        $this->assertEquals(0, $DB->get_field('local_multipicker', 'enabled', ['id' => $id]));
        $this->assertSame(1, $DB->count_records('local_multipicker_revision', ['profileid' => $id]));
        $args['state'] = json_encode(['ids' => [$id], 'action' => 'enable', 'sesskey' => 'invalid']);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        \local_multipicker_output_fragment_profiles($args);
    }

    /**
     * Active versions and versions referenced by migration plans outlive a configured limit.
     */
    public function test_retention_preserves_current_and_migration_references(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Pinned version']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        [, , $rules] = profiles::load($fixture['profileid']);
        $config = $rules->get_config();
        $config['name'] = 'Second name';
        profiles::save(json_encode($config), $fixture['profileid']);
        $config['name'] = 'Third name';
        profiles::save(json_encode($config), $fixture['profileid']);
        set_config('versionretentionmax', 1, 'local_multipicker');
        $this->assertGreaterThanOrEqual(1, profile_history::clean(time()));
        $this->assertTrue($DB->record_exists('local_multipicker_revision', ['id' => $plan->revisionid]));
        $this->assertSame(2, $DB->count_records('local_multipicker_revision', ['profileid' => $fixture['profileid']]));
    }

    /**
     * Empty work is cancelled and removal never deletes copied native files.
     */
    public function test_empty_plan_and_inactive_history_removal(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Safe removal']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        $run = migration_executor::queue($plan->id, $plan->digest);
        try {
            plan_history::delete($plan->id);
            $this->fail('Queued migrations cannot be removed.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('migrationinuse', $exception->errorcode);
        }
        migration_executor::execute($run);
        $empty = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        $this->assertSame('cancelled', $empty->state);
        $this->assertFalse(plan_history::has_work($empty->id));
        $files = $DB->get_records_menu('files', ['component' => 'mod_folder'], 'id', 'id, contenthash');
        plan_history::delete($empty->id);
        plan_history::delete($plan->id);
        $this->assertFalse($DB->record_exists('local_multipicker_plan', ['id' => $plan->id]));
        $this->assertSame($files, $DB->get_records_menu('files', ['component' => 'mod_folder'], 'id', 'id, contenthash'));
    }

    /**
     * Existing draft files occupy slots and recursive selections must fit before review.
     */
    public function test_native_capacity_accounts_for_existing_draft_files(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $draftid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string(['contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid, 'filepath' => '/',
            'filename' => 'existing.txt'], 'Existing material');
        $scope = ['draftid' => $draftid, 'limits' => ['maxfiles' => 2, 'selectionlimit' => 200]];
        $this->assertSame(1, selection_limits::capacity($scope));
        selection_limits::require_capacity($scope, ['file:first']);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('selectionfull', 'local_multipicker'));
        selection_limits::require_capacity($scope, ['file:first', 'file:second']);
    }
}
