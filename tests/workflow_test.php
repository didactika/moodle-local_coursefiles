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
use local_filebank\local\migration\plan_editor;
use local_filebank\local\migration\plan_selection;
use local_filebank\local\profile\profiles;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/workflow_fixture.php');

/**
 *  regression contracts.
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\plan_editor
 * @covers \local_filebank\local\migration\plan_history
 * @covers \local_filebank\local\migration\plan_selection
 * @covers \local_filebank\local\migration\target_binding
 * @covers \local_filebank\local\migration\migration_executor
 * @covers \local_filebank\local\maintenance\retention
 * @covers \local_filebank\local\profile\profiles
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\migration_planner::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\plan_editor::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\plan_history::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\plan_selection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\target_binding::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\migration_executor::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\maintenance\retention::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
final class workflow_test extends workflow_fixture {
    /**
     * Migration source types reuse public providers and exclude private assignment submissions.
     */
    public function test_public_activity_sources_copy_into_native_folders(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Public activity migration']);
        foreach (['resource' => 'Published resource', 'assign' => 'Public assignment handouts'] as $type => $name) {
            $config = \local_filebank\local\migration\migration_rules::from_form(
                [
                    'sectionstrategy' => 'ordinal',
                    'correspondencesjson' => json_encode(
                        [[
                            'sourcetype' => $type, 'sourcefield' => 'name', 'sourcemethod' => 'equals', 'sourcevalue' => $name,
                            'targetfield' => 'name', 'targetmethod' => 'equals', 'targetvalue' => 'Target materials',
                        ]]
                    ),
                ]
            );
            $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', 'edition', ['migration' => $config]);
            $this->assertSame('reviewable', $plan->state);
            $operations = $DB->get_records('local_filebank_operation', ['planid' => $plan->id]);
            $this->assertCount(1, $operations);
            $run = migration_executor::queue($plan->id, $plan->digest);
            $this->assertFalse(migration_executor::execute($run));
            $this->assertSame('completed', $DB->get_field('local_filebank_run', 'state', ['id' => $run]));
        }
        $context = \context_module::instance($fixture['targetcmid']);
        $files = get_file_storage()->get_area_files($context->id, 'mod_folder', 'content', 0, 'filename', false);
        $names = array_map(static fn($file) => $file->get_filename(), $files);
        $this->assertContains('resource.txt', $names);
        $this->assertContains('handout.txt', $names);
        $this->assertNotContains('student-private.txt', $names);
    }

    /**
     * Explicit destination choices preserve the old review and included exclusions.
     */
    public function test_resolve_destination_without_losing_selection(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Conflict review']);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'block',
            'correspondences' => [
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials']],
                ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Missing destination']],
            ]]];
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', 'edition', $options);
        $this->assertSame('reviewable', $plan->state, 'What waits for a decision never blocks the migration');
        $operations = $DB->get_records('local_filebank_operation', ['planid' => $plan->id]);
        $ids = array_keys($operations);
        plan_selection::save($plan->id, $plan->digest, $ids, [$ids[0]]);
        $plan = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);
        $plans = $DB->count_records('local_filebank_plan');
        $changed = plan_editor::resolve($plan->id, $plan->digest, 'issue', [0], 'folder:' . $fixture['targetcmid']);
        $this->assertSame(1, $changed);
        $this->assertSame($plans, $DB->count_records('local_filebank_plan'), 'Settling a decision creates no other migration');
        $this->assertNotSame($plan->configjson, $DB->get_field('local_filebank_plan', 'configjson', ['id' => $plan->id]));
        $this->assertSame('reviewable', $DB->get_field('local_filebank_plan', 'state', ['id' => $plan->id]));
        $this->assertSame(1, $DB->count_records('local_filebank_operation', ['planid' => $plan->id, 'included' => 0]));
        $state = plan_selection::state($plan->id, []);
        $this->assertTrue($state['cancontinue']);
        $this->assertSame(1, $state['included']);
        $this->assertSame(2, $state['total']);
    }

    /**
     * Missing destinations are only created at execution in the explicit existing section.
     */
    public function test_resolve_creation_and_execute_without_maintenance(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Create resolved destination']);
        $options = ['migration' => ['source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
            'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Missing destination'],
            'section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'block']];
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', 'edition', $options);
        $target = get_coursemodule_from_id('folder', $fixture['targetcmid']);
        $count = $DB->count_records('folder', ['course' => $target->course]);
        plan_editor::resolve($plan->id, $plan->digest, 'issue', [0], 'create:' . $target->section);
        $this->assertSame($count, $DB->count_records('folder', ['course' => $target->course]));
        $plan = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);
        $this->assertSame('reviewable', $plan->state);
        $CFG->maintenance_enabled = false;
        $run = migration_executor::queue($plan->id, $plan->digest);
        $more = migration_executor::execute($run);
        while ($more) {
            $more = migration_executor::execute($run);
        }
        $this->assertSame('completed', $DB->get_field('local_filebank_run', 'state', ['id' => $run]));
        $this->assertSame($count + 1, $DB->count_records('folder', ['course' => $target->course]));
        $this->assertFalse((bool)$CFG->maintenance_enabled);
    }

    /**
     * Whole-plan inclusion is independent of visible pages and queued runs cannot be bulk deleted.
     */
    public function test_whole_plan_selection_and_atomic_bulk_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Whole review']);
        $source = get_fast_modinfo($DB->get_field('course', 'id', ['shortname' => 'CB2025']))->get_instances_of('folder');
        $cm = reset($source);
        for ($i = 0; $i < 55; $i++) {
            get_file_storage()->create_file_from_string(
                ['contextid' => $cm->context->id, 'component' => 'mod_folder',
                    'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'extra-' . $i . '.txt',
                    'author' => 'Original teacher', 'license' => 'cc'],
                'Unique lesson ' . $i
            );
        }
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026');
        $ops = $DB->get_records('local_filebank_operation', ['planid' => $plan->id], 'id', '*', 0, 10);
        plan_selection::save($plan->id, $plan->digest, array_keys($ops), [], 'none');
        $state = plan_selection::state($plan->id, array_keys($ops));
        $this->assertSame(57, $state['total']);
        $this->assertSame(0, $state['included']);
        $this->assertTrue($state['empty']);
        $this->assertFalse($state['cancontinue']);
        plan_selection::save($plan->id, $state['digest'], array_keys($ops), [], 'all');
        $state = plan_selection::state($plan->id, array_keys($ops));
        $this->assertSame(57, $state['included']);
        $this->assertTrue($state['cancontinue']);
        $other = migration_planner::preview($fixture['profileid'], '2025', '2026');
        migration_executor::queue($plan->id, $state['digest']);
        try {
            \local_filebank\local\migration\plan_history::delete_many([$other->id, $plan->id]);
            $this->fail('A queued plan must protect the entire deletion');
        } catch (\moodle_exception $exception) {
            $this->assertSame('migrationinuse', $exception->errorcode);
        }
        $this->assertTrue($DB->record_exists('local_filebank_plan', ['id' => $other->id]));
        $this->assertTrue($DB->record_exists('local_filebank_plan', ['id' => $plan->id]));
        \local_filebank\local\migration\plan_history::delete_many([$other->id]);
        $this->assertFalse($DB->record_exists('local_filebank_plan', ['id' => $other->id]));
    }

    /**
     * A teacher can add unrelated material after review while the site stays available.
     */
    public function test_live_migration_keeps_files_added_after_review(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Available site']);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026');
        $file = get_file_storage()->create_file_from_string(
            [
                'contextid' => \context_module::instance($fixture['targetcmid'])->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
                'filename' => 'added-after-review.txt', 'author' => 'Destination teacher', 'license' => 'cc',
            ],
            'New material from the teacher'
        );
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('completed', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
        $saved = get_file_storage()->get_file_by_hash($file->get_pathnamehash());
        $this->assertSame('New material from the teacher', $saved->get_content());
        $this->assertSame('Destination teacher', $saved->get_author());
        $this->assertSame(
            4,
            count(
                get_file_storage()->get_area_files(
                    $saved->get_contextid(),
                    'mod_folder',
                    'content',
                    0,
                    'id',
                    false
                )
            )
        );
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
        $config = \local_filebank\form\profile_form::document($data);
        $this->assertSame([], $config['relationship']['filters']);
        $this->assertSame([], $config['publication']['allowed_sources']);
        $this->assertArrayNotHasKey('migration', $config);
        $this->assertArrayNotHasKey('display_label', $config['relationship']);
        $filters = [];
        for ($i = 0; $i < 9; $i++) {
            $filters['filter' . $i] = ['capture' => 'group', 'label' => 'Filter ' . $i, 'optional' => false];
        }
        $config['relationship']['filters'] = $filters;
        $this->assertCount(9, (new \local_filebank\local\profile\profile_config($config))->filter_definitions());
        $config['relationship']['filters']['tenth'] = ['capture' => 'group', 'label' => 'Too many'];
        $this->expectException(\moodle_exception::class);
        new \local_filebank\local\profile\profile_config($config);
    }

    /**
     * Several reviewed correspondences create only the missing destinations and remain idempotent.
     */
    public function test_native_creation_overwrite_and_identical_skip(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
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
        get_file_storage()->create_file_from_string(
            ['contextid' => $destination->context->id, 'component' => 'mod_folder',
                'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'lesson.txt',
                'author' => 'Previous author', 'license' => 'cc'],
            'previous lesson'
        );
        indexer::refresh($target->id);
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $options);
        $this->assertSame('reviewable', $plan->state);
        $this->assertSame(1, $DB->count_records('local_filebank_target', ['planid' => $plan->id]));
        $this->assertSame(1, $DB->count_records('local_filebank_operation', ['planid' => $plan->id, 'decision' => 'overwrite']));
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $more = migration_executor::execute($runid);
        while ($more) {
            $more = migration_executor::execute($runid);
        }
        $this->assertSame('completed', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
        $created = $DB->get_record('local_filebank_target', ['planid' => $plan->id]);
        $this->assertGreaterThan(0, (int)$created->cmid);
        $this->assertCount(
            2,
            get_file_storage()->get_area_files(
                \context_module::instance($created->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            )
        );
        $this->assertSame(
            'Original teacher',
            get_file_storage()->get_file(
                $destination->context->id,
                'mod_folder',
                'content',
                0,
                '/',
                'lesson.txt'
            )->get_author()
        );
        $second = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $options);
        $this->assertSame(
            4,
            $DB->count_records(
                'local_filebank_operation',
                ['planid' => $second->id, 'decision' => 'skip_identical']
            )
        );
        $this->assertSame(0, $DB->count_records('local_filebank_target', ['planid' => $second->id]));
        $CFG->maintenance_enabled = false;
    }

    /**
     * A source changed after review cannot create even an empty destination activity.
     */
    public function test_stale_source_does_not_create_destination(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Stale creation']);
        $options = ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'create',
            'correspondences' => [[
                'source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Published materials'],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Never created'],
            ]]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $options);
        foreach ($DB->get_records('local_filebank_operation', ['planid' => $plan->id]) as $operation) {
            get_file_storage()->get_file_by_hash($operation->sourcehash)->delete();
        }
        $CFG->maintenance_enabled = false;
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('partial', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
        $this->assertFalse($DB->record_exists('folder', ['course' => $fixture['targetcourse'], 'name' => 'Never created']));
        $this->assertSame(0, (int)$DB->get_field('local_filebank_target', 'cmid', ['planid' => $plan->id]));
        $CFG->maintenance_enabled = false;
    }
}
