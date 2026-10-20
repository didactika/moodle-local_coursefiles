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
 * Administration authorization and bounded review tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\catalog\indexer;
use local_filebank\local\maintenance\retention;
use local_filebank\local\migration\migration_executor;
use local_filebank\local\migration\migration_planner;
use local_filebank\local\migration\plan_selection;
use local_filebank\local\profile\profiles;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/workflow_fixture.php');

/**
 * Authorization regression contracts.
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\plan_editor
 * @covers \local_filebank\local\migration\plan_history
 * @covers \local_filebank\local\migration\plan_selection
 * @covers \local_filebank\local\migration\target_binding
 * @covers \local_filebank\local\migration\migration_executor
 * @covers \local_filebank\local\maintenance\retention
 * @covers \local_filebank\local\profile\profiles
 */
final class workflow_authorization_test extends workflow_fixture {
    /**
     * Missing sources are informational; byte-identical targets retain their own attribution.
     */
    public function test_missing_source_and_identical_content_do_not_block_other_files(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Identical content']);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'identical_content' => 'skip',
            'correspondences' => [
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Missing source'],
                    'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                    'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
            ]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        $file = get_file_storage()->create_file_from_string(
            [
                'contextid' => \context_module::instance($fixture['targetcmid'])->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
                'filename' => 'lesson.txt', 'author' => 'Destination teacher', 'license' => 'cc',
            ],
            'Shared lesson content'
        );
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $options);
        $this->assertSame('reviewable', $plan->state);
        $document = json_decode($plan->configjson, true);
        $this->assertSame([], $document['issues'], 'A folder that the source does not have is not an error');
        $this->assertSame(1, $document['notes']['nosourcefolder']);
        $this->assertSame(
            1,
            $DB->count_records(
                'local_filebank_operation',
                ['planid' => $plan->id, 'decision' => 'skip_content']
            )
        );
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('completed', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
        $this->assertSame('Destination teacher', get_file_storage()->get_file_by_hash($file->get_pathnamehash())->get_author());
        $CFG->maintenance_enabled = false;
    }

    /**
     * A saved approval cannot enqueue a profile that has been disabled afterward.
     */
    public function test_disabled_profile_cannot_queue_previous_approval(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Disabled approval']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026');
        profiles::change_state([$fixture['profileid']], 'disable');
        $CFG->maintenance_enabled = false;
        $this->expectException(\dml_missing_record_exception::class);
        migration_executor::queue($plan->id, $plan->digest);
    }

    /**
     * Distinct source folders targeting the same folder retain their own group selectors.
     */
    public function test_review_keeps_distinct_source_folder_groups(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Separate source folders']);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id, 'name' => 'Secondary materials']);
        get_file_storage()->create_file_from_string(
            ['contextid' => \context_module::instance($folder->cmid)->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
                'filename' => 'secondary.txt', 'author' => 'Original teacher', 'license' => 'cc'],
            'secondary'
        );
        \local_filebank\local\catalog\material_source::publish($fixture['profileid'], $folder->cmid, false);
        indexer::refresh($source->id);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'correspondences' => []],
            'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        foreach (['Published materials', 'Secondary materials'] as $name) {
            $options['migration']['correspondences'][] = [
                'source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => $name],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials'],
            ];
        }
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $options);
        $data = \local_filebank\local\migration\view\migration_files::folders($plan->id, '', 0, 25);
        $this->assertSame(2, $data['total']);
        $this->assertNotSame($data['folders'][0]['sourcecmid'], $data['folders'][1]['sourcecmid']);
        $this->assertSame(3, $data['folders'][0]['total'] + $data['folders'][1]['total']);
    }

    /**
     * Folder/course actions cover files beyond the rendered review page.
     */
    public function test_whole_group_selection_changes_approval_across_pages(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Group migration']);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        for ($i = 0; $i < 61; $i++) {
            get_file_storage()->create_file_from_string(
                ['contextid' => $folder->context->id,
                    'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                    'filepath' => '/', 'filename' => 'group-' . $i . '.txt', 'author' => 'Teacher', 'license' => 'cc'],
                'item ' . $i
            );
        }
        indexer::refresh($source->id);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026');
        plan_selection::save(
            $plan->id,
            $plan->digest,
            [],
            [],
            'group',
            ['courseid' => (int)$source->id, 'cmid' => (int)$folder->id, 'included' => 0]
        );
        $this->assertSame(0, $DB->count_records('local_filebank_operation', ['planid' => $plan->id, 'included' => 1]));
        $changed = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);
        $this->assertNotSame($plan->digest, $changed->digest);
    }

    /**
     * Expiration preserves active plans, current runs and authorship records.
     */
    public function test_retention_excludes_active_and_recent_runs(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Retention migration']);
        $expired = migration_planner::preview($fixture['profileid'], '2025', '2026');
        $active = migration_planner::preview($fixture['profileid'], '2025', '2026');
        $now = time();
        $DB->set_field('local_filebank_plan', 'timecreated', $now - 400 * DAYSECS);
        $DB->insert_record(
            'local_filebank_run',
            (object)['planid' => $active->id, 'userid' => get_admin()->id,
                'state' => 'queued', 'cancelled' => 0, 'timecreated' => $now - 400 * DAYSECS,
                'timemodified' => $now - 400 * DAYSECS]
        );
        $DB->set_field('local_filebank_log', 'timecreated', $now - 400 * DAYSECS);
        $counts = retention::clean($now);
        $this->assertSame(1, $counts['plans']);
        $this->assertGreaterThan(0, $counts['logs']);
        $this->assertFalse($DB->record_exists('local_filebank_plan', ['id' => $expired->id]));
        $this->assertTrue($DB->record_exists('local_filebank_plan', ['id' => $active->id]));
        set_config('migrationretentiondays', 0, 'local_filebank');
        $DB->set_field('local_filebank_run', 'state', 'completed', ['planid' => $active->id]);
        $this->assertSame(0, retention::clean($now)['plans']);
    }

    /**
     * Deleting a profile removes it from normal use while preserving historical references.
     */
    public function test_profile_removal_keeps_revisions_and_history(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Removed profile']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026');
        profiles::change_state([$fixture['profileid']], 'disable');
        $this->assertSame(0, (int)$DB->get_field('local_filebank', 'enabled', ['id' => $fixture['profileid']]));
        profiles::change_state([$fixture['profileid']], 'enable');
        profiles::change_state([$fixture['profileid']], 'delete');
        $this->assertSame(1, (int)$DB->get_field('local_filebank', 'deleted', ['id' => $fixture['profileid']]));
        $this->assertTrue($DB->record_exists('local_filebank_revision', ['id' => $plan->revisionid]));
        $this->assertTrue($DB->record_exists('local_filebank_plan', ['id' => $plan->id]));
        $this->expectException(\dml_missing_record_exception::class);
        profiles::load($fixture['profileid']);
    }
}
