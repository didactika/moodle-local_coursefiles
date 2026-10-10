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


/**
 * Tests actual file, identity, authorization and migration contracts.
 *
 * @covers \local_filebank\local\profile\profile_config
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\migration\copy_decision
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\migration_executor
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profile_config::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\catalog::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\copy_decision::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\migration_planner::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\migration\migration_executor::class)]
final class profile_config_test extends \advanced_testcase {
    /**
     * Either course can tighten an editable site policy before a folder write.
     */
    public function test_course_locks_are_checked_at_both_ends_of_folder_copy(): void {
        global $CFG;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        set_config('author_course_override', 'tighten_only', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        foreach ([$target->id, $source->id] as $courseid) {
            \local_filebank\local\policy\author_policy::save($source->id, 'inherit');
            \local_filebank\local\policy\author_policy::save($target->id, 'inherit');
            \local_filebank\local\policy\author_policy::save($courseid, 'preserve_locked');
            try {
                \local_filebank\local\migration\folder_destination::copy(reset($files), $tfolder->cmid);
                $this->fail('A course lock must refuse the copy.');
            } catch (\moodle_exception $exception) {
                $this->assertSame('lockedunsupported', $exception->errorcode);
            }
            $this->assertCount(0, $fs->get_area_files(
                \context_module::instance($tfolder->cmid)->id,
                'mod_folder',
                'content',
                0,
                'id',
                false
            ));
        }
    }

    /**
     * Native folder copying preserves existing nested files and their metadata.
     */
    public function test_folder_copy_preserves_existing_destination_files(): void {
        global $CFG;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $fs = get_file_storage();
        $context = \context_module::instance($tfolder->cmid);
        $existing = $fs->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
            'itemid' => 0, 'filepath' => '/existing/', 'filename' => 'teacher-notes.txt',
            'author' => 'Destination author', 'license' => 'allrightsreserved',
        ], 'Existing teaching notes');
        $sourcefiles = $fs->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        $saved = \local_filebank\local\migration\folder_destination::copy(reset($sourcefiles), $tfolder->cmid);
        $kept = $fs->get_file($context->id, 'mod_folder', 'content', 0, '/existing/', 'teacher-notes.txt');
        $this->assertNotFalse($kept);
        $this->assertSame((int)$existing->get_id(), (int)$kept->get_id());
        $this->assertSame('Existing teaching notes', $kept->get_content());
        $this->assertSame('Destination author', $kept->get_author());
        $this->assertSame('allrightsreserved', $kept->get_license());
        $this->assertSame('Original author', $saved->get_author());
        $this->assertCount(2, $fs->get_area_files($context->id, 'mod_folder', 'content', 0, 'id', false));
    }

    /**
     * Calling the destination adapter directly cannot bypass the strict policy.
     */
    public function test_folder_adapter_checks_policy_before_writing_files(): void {
        global $CFG, $DB;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        $CFG->maintenance_enabled = false;
        $files = get_file_storage()->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        $file = reset($files);
        $draftcount = $DB->count_records('files', ['component' => 'user', 'filearea' => 'draft']);
        try {
            \local_filebank\local\migration\folder_destination::copy($file, $tfolder->cmid);
            $this->fail('The adapter must refuse a strict copy.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('lockedunsupported', $exception->errorcode);
        }
        $this->assertSame($draftcount, $DB->count_records('files', ['component' => 'user', 'filearea' => 'draft']));
        $this->assertCount(0, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
    }

    /**
     * Load the institutional preset.
     *
     * @return array Document.
     */
    private function config(): array {
        return json_decode(file_get_contents(__DIR__ . '/../examples/institutional-profile.json'), true);
    }

    /**
     * Optional editions resolve without inventing a year.
     */
    public function test_optional_edition_and_family(): void {
        $rules = new profile_config($this->config());
        $id = '550e8400-e29b-41d4-a716-446655440000';
        $a = $rules->resolve((object)['idnumber' => $id . '||MAT101||2025']);
        $b = $rules->resolve((object)['idnumber' => $id . '||MAT101||2026']);
        $c = $rules->resolve((object)['idnumber' => $id . '||MAT101']);
        $this->assertSame($a['familykey'], $b['familykey']);
        $this->assertSame('2025', $a['edition']);
        $this->assertNull($c['edition']);
    }

    /**
     * Conflicting parsers cannot silently choose a family.
     */
    public function test_ambiguous_parsers_are_rejected(): void {
        $config = $this->config();
        $config['relationship']['parsers'][] = ['field' => 'shortname',
            'pattern' => '~^(?<family>.+)$~', 'required_captures' => ['family']];
        $rules = new profile_config($config);
        $this->expectException(\moodle_exception::class);
        $rules->resolve((object)['idnumber' => '550e8400-e29b-41d4-a716-446655440000||MAT101||2025',
            'shortname' => 'another-family']);
    }

    /**
     * Invalid PCRE is a configuration failure.
     */
    public function test_invalid_pattern_is_rejected(): void {
        $config = $this->config();
        $config['relationship']['parsers'][0]['pattern'] = '~[~';
        $this->expectException(\moodle_exception::class);
        new profile_config($config);
    }

    /**
     * Byte equality alone never permits relabeling authors.
     */
    public function test_attribution_conflict_is_distinct(): void {
        $source = ['contenthash' => str_repeat('a', 40), 'filesize' => 4, 'author' => 'Alice', 'license' => 'cc'];
        $other = $source;
        $other['author'] = 'Bob';
        $this->assertSame('blocked_attribution', copy_decision::classify($source, $other));
        $this->assertSame('skip_identical', copy_decision::classify($source, $source));
        $other['contenthash'] = str_repeat('b', 40);
        $this->assertSame('blocked_content', copy_decision::classify($source, $other));
    }

    /**
     * Create annual folder materials.
     *
     * @return array Profile, source, target, source folder, target folder.
     */
    private function fixture(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        $profileid = profiles::save(json_encode($this->config()));
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $source = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MAT101||2025']);
        $target = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MAT101||2026']);
        $sfolder = $this->getDataGenerator()->create_module('folder', [
            'course' => $source->id, 'name' => 'materiales_de_clase.module', 'section' => 1]);
        $tfolder = $this->getDataGenerator()->create_module('folder', [
            'course' => $target->id, 'name' => 'materiales_de_clase.module', 'section' => 1]);
        get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($sfolder->cmid)->id, 'component' => 'mod_folder',
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/week1/', 'filename' => 'slides.pdf',
            'author' => 'Original author', 'license' => 'cc',
        ], 'identical teaching content');
        indexer::refresh($source->id);
        indexer::refresh($target->id);
        return [$profileid, $source, $target, $sfolder, $tfolder];
    }

    /**
     * Unauthorized users cannot enumerate teacher materials.
     */
    public function test_catalog_requires_target_authority(): void {
        [$profileid, $source, $target] = $this->fixture();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        catalog::search($target->id, $profileid);
    }

    /**
     * Same bytes in two editions produce one asset and filtered origins.
     */
    public function test_grouping_and_edition_filter(): void {
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        $file = get_file_storage()->get_file(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            '/week1/',
            'slides.pdf'
        );
        get_file_storage()->create_file_from_storedfile([
            'contextid' => \context_module::instance($tfolder->cmid)->id, 'component' => 'mod_folder',
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'renamed.pdf',
        ], $file);
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
     * Locked authors are blocked before any destination write.
     */
    public function test_locked_workflow_is_explicitly_blocked(): void {
        [$profileid] = $this->fixture();
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
        $this->assertSame('blocked', $plan->state);
        $document = json_decode($plan->configjson, true);
        $this->assertSame('blocked_policy', $GLOBALS['DB']->get_field(
            'local_filebank_operation',
            'decision',
            ['planid' => $plan->id]
        ));
    }

    /**
     * Online copies preserve author, path and source; reruns add nothing.
     */
    public function test_reviewed_copy_is_idempotent(): void {
        global $CFG, $DB;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
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
        $second = migration_planner::preview($profileid, '2025', '2026', true);
        $this->assertSame('skip_identical', $DB->get_field('local_filebank_operation', 'decision', ['planid' => $second->id]));
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
    }

    /**
     * Existing identical content under another name is not copied again.
     */
    public function test_cross_path_duplicate_is_skipped(): void {
        global $CFG, $DB;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        $sourcefile = get_file_storage()->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        get_file_storage()->create_file_from_storedfile([
            'contextid' => \context_module::instance($tfolder->cmid)->id,
            'filepath' => '/', 'filename' => 'renamed.pdf',
        ], reset($sourcefile));
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
        $this->assertSame('skip_content', $DB->get_field('local_filebank_operation', 'decision', ['planid' => $plan->id]));
        $this->assertSame('cancelled', $plan->state);
        $this->assertFalse($DB->record_exists('local_filebank_run', ['planid' => $plan->id]));
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
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
        $plan = migration_planner::preview($profileid, '2025', '2026', true, 'cohort');
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
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
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
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
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
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
        $this->assertSame('pending', $DB->get_field('local_filebank_operation', 'state', ['id' => $ids[1]]));
        $this->expectException(\moodle_exception::class);
        \local_filebank\local\migration\plan_selection::save($plan->id, $changed->digest, [], [], 'all');
    }

    /**
     * New profile revisions invalidate queued plans before course writes.
     */
    public function test_profile_revision_blocks_queued_plan(): void {
        global $CFG, $DB;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
        $runid = migration_executor::queue($plan->id, $plan->digest);
        profiles::save(json_encode($this->config()), $profileid);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('blocked', $DB->get_field('local_filebank_run', 'state', ['id' => $runid]));
        $this->assertCount(0, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
    }

    /**
     * A changed destination layout cannot receive a reviewed copy.
     */
    public function test_changed_destination_is_blocked(): void {
        global $CFG, $DB;
        [$profileid, $source, $target, $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $plan = migration_planner::preview($profileid, '2025', '2026', true);
        $runid = migration_executor::queue($plan->id, $plan->digest);
        $DB->set_field('folder', 'name', 'changed', ['id' => $tfolder->id]);
        rebuild_course_cache($target->id, true);
        $this->assertFalse(migration_executor::execute($runid));
        $this->assertSame('blocked', $DB->get_field('local_filebank_operation', 'state', ['planid' => $plan->id]));
        $this->assertCount(0, get_file_storage()->get_area_files(
            \context_module::instance($tfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
    }
}
