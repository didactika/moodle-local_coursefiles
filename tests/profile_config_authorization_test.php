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
 * Authorization regression contracts.
 * @covers \local_filebank\local\profile\profile_config
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\migration\copy_decision
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\migration_executor
 */
final class profile_config_authorization_test extends profile_config_fixture {
    /**
     * Same bytes in two editions produce one asset and filtered origins.
     */
    public function test_grouping_and_edition_filter(): void {

        [$profileid, , $target, $sfolder, $tfolder] = $this->fixture();
        $file = get_file_storage()->get_file(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            '/week1/',
            'slides.pdf'
        );
        get_file_storage()->create_file_from_storedfile(
            [
                'contextid' => \context_module::instance($tfolder->cmid)->id, 'component' => 'mod_folder',
                'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'renamed.pdf',
            ],
            $file
        );
        indexer::refresh($target->id);
        $all = catalog::search($target->id, $profileid);
        $this->assertCount(1, $all['assets']);
        $this->assertCount(2, $all['assets'][0]['occurrences']);
        $filtered = catalog::search($target->id, $profileid, ['edition' => '2025']);
        $this->assertCount(1, $filtered['assets'][0]['occurrences']);
        $this->assertSame('slides.pdf', $filtered['assets'][0]['occurrences'][0]['filename']);
    }

    /**
     * A stale index must not expose a now-hidden source.
     */
    public function test_live_visibility_beats_stale_index(): void {
        global $DB;
        [$profileid, $source, $target, $sfolder] = $this->fixture();
        $DB->set_field('course_modules', 'visible', 0, ['id' => $sfolder->cmid]);
        rebuild_course_cache($source->id, true);
        $this->assertCount(0, catalog::search($target->id, $profileid)['assets']);
    }

    /**
     * A file whose author is locked is kept out of the migration before any destination write.
     */
    public function test_locked_workflow_is_explicitly_blocked(): void {

        [$profileid] = $this->fixture();
        $plan = migration_planner::preview($profileid, '2025', '2026');
        $this->assertSame('reviewable', $plan->state);
        json_decode($plan->configjson, true);
        $this->assertSame(
            'skip_conflict',
            $GLOBALS['DB']->get_field(
                'local_filebank_operation',
                'decision',
                ['planid' => $plan->id]
            )
        );
    }

    /**
     * Online copies preserve author, path and source; reruns add nothing.
     */
    public function test_reviewed_copy_is_idempotent(): void {

        global $CFG, $DB;
        [$profileid, , , $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026');
        $this->assertSame('reviewable', $plan->state);
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame(
            'completed',
            $DB->get_field('local_filebank_run', 'state', ['id' => $runid]),
            json_encode($DB->get_records('local_filebank_operation', ['planid' => $plan->id]))
        );
        $saved = get_file_storage()->get_file(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            '/week1/',
            'slides.pdf'
        );
        $this->assertNotFalse($saved);
        $this->assertSame('Original author', $saved->get_author());
        $second = migration_planner::preview($profileid, '2025', '2026');
        $this->assertSame('skip_identical', $DB->get_field('local_filebank_operation', 'decision', ['planid' => $second->id]));
        $this->assertCount(
            1,
            get_file_storage()->get_area_files(
                \context_module::instance($tfolder->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            )
        );
        $this->assertCount(
            1,
            get_file_storage()->get_area_files(
                \context_module::instance($sfolder->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            )
        );
    }

    /**
     * Existing identical content under another name is not copied again.
     */
    public function test_cross_path_duplicate_is_skipped(): void {

        global $CFG, $DB;
        [$profileid, , , $sfolder, $tfolder] = $this->fixture();
        $sourcefile = get_file_storage()->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        get_file_storage()->create_file_from_storedfile(
            [
                'contextid' => \context_module::instance($tfolder->cmid)->id,
                'filepath' => '/', 'filename' => 'renamed.pdf',
            ],
            reset($sourcefile)
        );
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026');
        $this->assertSame('skip_content', $DB->get_field('local_filebank_operation', 'decision', ['planid' => $plan->id]));
        $this->assertSame('reviewable', $plan->state);
        $this->assertFalse($DB->record_exists('local_filebank_run', ['planid' => $plan->id]));
        $this->assertCount(
            1,
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

    /**
     * Configured captures support matching without an annual edition rule.
     */
    public function test_migration_uses_configured_dimension(): void {
        [$profileid, $source, $target] = $this->fixture();
        $config = $this->config();
        unset($config['relationship']['edition']);
        $config['relationship']['filters'] = ['cohort' => ['capture' => 'edition', 'label' => 'Cohort']];
        profiles::save(json_encode($config), $profileid);
        foreach ([$source, $target] as $course) {
            indexer::refresh($course->id);
        }
        $plan = migration_planner::preview($profileid, '2025', '2026', 'cohort');
        $document = json_decode($plan->configjson, true);
        $this->assertSame('cohort', $document['scopekey']);
        $this->assertSame(1, $document['operationcount']);
        $this->assertArrayNotHasKey('operations', $document);
    }

    /**
     * Exclusions invalidate approval, persist across pages and never execute.
     */
    public function test_migration_exclusions_are_not_executed(): void {

        global $CFG, $DB;
        [$profileid, $source, , $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $sourcefiles = get_file_storage()->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        $sourcefile = reset($sourcefiles);
        get_file_storage()->create_file_from_storedfile(['filename' => 'second.pdf'], $sourcefile);
        indexer::refresh($source->id);
        $plan = migration_planner::preview($profileid, '2025', '2026');
        $ids = array_map('intval', $DB->get_fieldset_select('local_filebank_operation', 'id', 'planid = ?', [$plan->id]));
        \local_filebank\local\migration\plan_selection::save($plan->id, $plan->digest, $ids, [$ids[0]]);
        $changed = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);
        $this->assertNotSame($plan->digest, $changed->digest);
        try {
            migration_executor::queue($plan->id, $plan->digest);
            $this->fail('Old approval must not queue a changed selection.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('planstale', $exception->errorcode);
        }
        $run = migration_executor::queue($plan->id, $changed->digest);
        $this->assertFalse(migration_executor::execute($run));
        $this->assertCount(
            1,
            get_file_storage()->get_area_files(
                \context_module::instance($tfolder->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            )
        );
        $this->assertSame('pending', $DB->get_field('local_filebank_operation', 'state', ['id' => $ids[1]]));
        $this->expectException(\moodle_exception::class);
        \local_filebank\local\migration\plan_selection::save($plan->id, $changed->digest, [], [], 'all');
    }

    /**
     * New profile revisions invalidate queued plans before course writes.
     */
    public function test_profile_revision_blocks_queued_plan(): void {

        global $CFG, $DB;
        [$profileid, , , , $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026');
        $runid = migration_executor::queue($plan->id, $plan->digest);
        profiles::save(json_encode($this->config()), $profileid);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('blocked', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
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
