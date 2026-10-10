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
 * Administration authorization and bounded review tests.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker;

use local_multipicker\local\catalog\indexer;
use local_multipicker\local\maintenance\retention;
use local_multipicker\local\migration\migration_executor;
use local_multipicker\local\migration\migration_planner;
use local_multipicker\local\migration\plan_selection;
use local_multipicker\local\profile\profiles;

/**
 * Exercises native destinations, history retention and profile lifecycle.
 *
 * @covers \local_multipicker\local\migration\migration_planner
 * @covers \local_multipicker\local\migration\target_binding
 * @covers \local_multipicker\local\migration\migration_executor
 * @covers \local_multipicker\local\maintenance\retention
 * @covers \local_multipicker\local\profile\profiles
 */
final class workflow_test extends \advanced_testcase {
    /**
     * A teacher can add unrelated material after review while the site stays available.
     */
    public function test_live_migration_keeps_files_added_after_review(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Available site']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($fixture['targetcmid'])->id,
            'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
            'filename' => 'added-after-review.txt', 'author' => 'Destination teacher', 'license' => 'cc',
        ], 'New material from the teacher');
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('completed', $DB->get_field('local_multipicker_run', 'state', ['id' => $runid]));
        $saved = get_file_storage()->get_file_by_hash($file->get_pathnamehash());
        $this->assertSame('New material from the teacher', $saved->get_content());
        $this->assertSame('Destination teacher', $saved->get_author());
        $this->assertSame(4, count(get_file_storage()->get_area_files(
            $saved->get_contextid(),
            'mod_folder',
            'content',
            0,
            'id',
            false
        )));
    }

    /**
     * A blank guided form builds its own validated document; no example is inherited.
     */
    public function test_new_guided_profile_and_nine_filter_limit(): void {
        $this->resetAfterTest();
        $data = ['editmode' => 'guided', 'profilename' => 'New profile', 'coursefield' => 'shortname',
            'coursepattern' => '~^(?<group>.+)$~', 'familytemplate' => '${group}', 'filtersjson' => '[]',
            'guidedsettings' => 1, 'publicationmode' => 'source-access-only', 'sourcesdefined' => 1,
            'displaytemplate' => ''];
        $config = \local_multipicker\form\profile_form::document($data);
        $this->assertSame([], $config['relationship']['filters']);
        $this->assertSame([], $config['publication']['allowed_sources']);
        $this->assertArrayNotHasKey('migration', $config);
        $this->assertArrayNotHasKey('display_label', $config['relationship']);
        $filters = [];
        for ($i = 0; $i < 9; $i++) {
            $filters['filter' . $i] = ['capture' => 'group', 'label' => 'Filter ' . $i, 'optional' => false];
        }
        $config['relationship']['filters'] = $filters;
        $this->assertCount(9, (new \local_multipicker\local\profile\profile_config($config))->filter_definitions());
        $config['relationship']['filters']['tenth'] = ['capture' => 'group', 'label' => 'Too many'];
        $this->expectException(\moodle_exception::class);
        new \local_multipicker\local\profile\profile_config($config);
    }

    /**
     * Several reviewed correspondences create only the missing destinations and remain idempotent.
     */
    public function test_native_creation_overwrite_and_identical_skip(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Native migration']);
        $target = $DB->get_record('course', ['shortname' => 'CB2026'], '*', MUST_EXIST);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'create',
            'same_path_different_content' => 'overwrite', 'correspondences' => [
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Created materials']],
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
            ]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        $destinations = get_fast_modinfo($target)->get_instances_of('folder');
        $destination = reset($destinations);
        get_file_storage()->create_file_from_string(['contextid' => $destination->context->id, 'component' => 'mod_folder',
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'lesson.txt',
            'author' => 'Previous author', 'license' => 'cc'], 'previous lesson');
        indexer::refresh($target->id);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true, '', $options);
        $this->assertSame('reviewable', $plan->state);
        $this->assertSame(1, $DB->count_records('local_multipicker_target', ['planid' => $plan->id]));
        $this->assertSame(1, $DB->count_records('local_multipicker_operation', ['planid' => $plan->id, 'decision' => 'overwrite']));
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $more = migration_executor::execute($runid);
        while ($more) {
            $more = migration_executor::execute($runid);
        }
        $this->assertSame('completed', $DB->get_field('local_multipicker_run', 'state', ['id' => $runid]));
        $created = $DB->get_record('local_multipicker_target', ['planid' => $plan->id]);
        $this->assertGreaterThan(0, (int)$created->cmid);
        $this->assertCount(2, get_file_storage()->get_area_files(
            \context_module::instance($created->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
        $this->assertSame('Original teacher', get_file_storage()->get_file(
            $destination->context->id,
            'mod_folder',
            'content',
            0,
            '/',
            'lesson.txt'
        )->get_author());
        $second = migration_planner::preview($fixture['profileid'], '2025', '2026', true, '', $options);
        $this->assertSame(4, $DB->count_records(
            'local_multipicker_operation',
            ['planid' => $second->id, 'decision' => 'skip_identical']
        ));
        $this->assertSame(0, $DB->count_records('local_multipicker_target', ['planid' => $second->id]));
        $CFG->maintenance_enabled = false;
    }


    /**
     * A source changed after review cannot create even an empty destination activity.
     */
    public function test_stale_source_does_not_create_destination(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Stale creation']);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'create',
            'correspondences' => [[
                'source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Never created'],
            ]]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true, '', $options);
        foreach ($DB->get_records('local_multipicker_operation', ['planid' => $plan->id]) as $operation) {
            get_file_storage()->get_file_by_hash($operation->sourcehash)->delete();
        }
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('partial', $DB->get_field('local_multipicker_run', 'state', ['id' => $runid]));
        $this->assertFalse($DB->record_exists('folder', ['course' => $fixture['targetcourse'], 'name' => 'Never created']));
        $this->assertSame(0, (int)$DB->get_field('local_multipicker_target', 'cmid', ['planid' => $plan->id]));
        $CFG->maintenance_enabled = false;
    }

    /**
     * Missing sources are informational; byte-identical targets retain their own attribution.
     */
    public function test_missing_source_and_identical_content_do_not_block_other_files(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Identical content']);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'identical_content' => 'skip',
            'correspondences' => [
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Missing source'],
                    'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                    'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
            ]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        $file = get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($fixture['targetcmid'])->id,
            'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
            'filename' => 'lesson.txt', 'author' => 'Destination teacher', 'license' => 'cc',
        ], 'Shared lesson content');
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true, '', $options);
        $this->assertSame('reviewable', $plan->state);
        $document = json_decode($plan->configjson, true);
        $this->assertSame('sourcefoldermissing', $document['issues'][0]['reason']);
        $this->assertFalse($document['issues'][0]['blocking']);
        $this->assertSame(1, $DB->count_records(
            'local_multipicker_operation',
            ['planid' => $plan->id, 'decision' => 'skip_content']
        ));
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('completed', $DB->get_field('local_multipicker_run', 'state', ['id' => $runid]));
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
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Disabled approval']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
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
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Separate source folders']);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id, 'name' => 'Secondary materials']);
        get_file_storage()->create_file_from_string(['contextid' => \context_module::instance($folder->cmid)->id,
            'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
            'filename' => 'secondary.txt', 'author' => 'Original teacher', 'license' => 'cc'], 'secondary');
        \local_multipicker\local\catalog\material_source::publish($fixture['profileid'], $folder->cmid, false);
        indexer::refresh($source->id);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'correspondences' => []],
            'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        foreach (['Published materials', 'Secondary materials'] as $name) {
            $options['migration']['correspondences'][] = [
                'source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => $name],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials'],
            ];
        }
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true, '', $options);
        $data = \local_multipicker\output\migration_report::data($plan->id);
        $this->assertCount(2, $data['groups']);
        $this->assertCount(2, $data['courses'][0]['folders']);
        $this->assertNotSame($data['groups'][0]['groupcmid'], $data['groups'][1]['groupcmid']);
        $this->assertSame(3, $data['courses'][0]['coursetotal']);
    }

    /**
     * Folder/course actions cover files beyond the rendered review page.
     */
    public function test_whole_group_selection_changes_approval_across_pages(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Group migration']);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        for ($i = 0; $i < 61; $i++) {
            get_file_storage()->create_file_from_string(['contextid' => $folder->context->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                'filepath' => '/', 'filename' => 'group-' . $i . '.txt', 'author' => 'Teacher', 'license' => 'cc'], 'item ' . $i);
        }
        indexer::refresh($source->id);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        plan_selection::save(
            $plan->id,
            $plan->digest,
            [],
            [],
            'group',
            ['courseid' => (int)$source->id, 'cmid' => (int)$folder->id, 'included' => 0]
        );
        $this->assertSame(0, $DB->count_records('local_multipicker_operation', ['planid' => $plan->id, 'included' => 1]));
        $changed = $DB->get_record('local_multipicker_plan', ['id' => $plan->id]);
        $this->assertNotSame($plan->digest, $changed->digest);
    }

    /**
     * Expiration preserves active plans, current runs and authorship records.
     */
    public function test_retention_excludes_active_and_recent_runs(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Retention migration']);
        $expired = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        $active = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        $now = time();
        $DB->set_field('local_multipicker_plan', 'timecreated', $now - 400 * DAYSECS);
        $DB->insert_record('local_multipicker_run', (object)['planid' => $active->id, 'userid' => get_admin()->id,
            'state' => 'queued', 'cancelled' => 0, 'timecreated' => $now - 400 * DAYSECS,
            'timemodified' => $now - 400 * DAYSECS]);
        $DB->set_field('local_multipicker_log', 'timecreated', $now - 400 * DAYSECS);
        $counts = retention::clean($now);
        $this->assertSame(1, $counts['plans']);
        $this->assertGreaterThan(0, $counts['logs']);
        $this->assertFalse($DB->record_exists('local_multipicker_plan', ['id' => $expired->id]));
        $this->assertTrue($DB->record_exists('local_multipicker_plan', ['id' => $active->id]));
        set_config('migrationretentiondays', 0, 'local_multipicker');
        $DB->set_field('local_multipicker_run', 'state', 'completed', ['planid' => $active->id]);
        $this->assertSame(0, retention::clean($now)['plans']);
    }

    /**
     * Deleting a profile removes it from normal use while preserving historical references.
     */
    public function test_profile_removal_keeps_revisions_and_history(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_multipicker')
            ->create_migration_fixture(['name' => 'Removed profile']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', true);
        profiles::change_state([$fixture['profileid']], 'disable');
        $this->assertSame(0, (int)$DB->get_field('local_multipicker', 'enabled', ['id' => $fixture['profileid']]));
        profiles::change_state([$fixture['profileid']], 'enable');
        profiles::change_state([$fixture['profileid']], 'delete');
        $this->assertSame(1, (int)$DB->get_field('local_multipicker', 'deleted', ['id' => $fixture['profileid']]));
        $this->assertTrue($DB->record_exists('local_multipicker_revision', ['id' => $plan->revisionid]));
        $this->assertTrue($DB->record_exists('local_multipicker_plan', ['id' => $plan->id]));
        $this->expectException(\dml_missing_record_exception::class);
        profiles::load($fixture['profileid']);
    }
}
