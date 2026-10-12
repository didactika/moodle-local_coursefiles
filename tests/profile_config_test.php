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
 *  regression contracts.
 * @covers \local_filebank\local\profile\profile_config
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\migration\copy_decision
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\migration_executor
 */
final class profile_config_test extends profile_config_fixture {
    /**
     * Either course can tighten an editable site policy before a folder write.
     */
    public function test_course_locks_are_checked_at_both_ends_of_folder_copy(): void {

        global $CFG;
        [, $source, $target, $sfolder, $tfolder] = $this->fixture();
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
                \local_filebank\local\migration\folder_destination::copy(reset($files), $tfolder->cmid, false);
                $this->fail('A course lock must refuse the copy.');
            } catch (\moodle_exception $exception) {
                $this->assertSame('lockedunsupported', $exception->errorcode);
            }
            $this->assertCount(
                0,
                $fs->get_area_files(
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

    /**
     * Native folder copying preserves existing nested files and their metadata.
     */
    public function test_folder_copy_preserves_existing_destination_files(): void {

        global $CFG;
        [, , , $sfolder, $tfolder] = $this->fixture();
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $CFG->maintenance_enabled = false;
        $fs = get_file_storage();
        $context = \context_module::instance($tfolder->cmid);
        $existing = $fs->create_file_from_string(
            [
                'contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
                'itemid' => 0, 'filepath' => '/existing/', 'filename' => 'teacher-notes.txt',
                'author' => 'Destination author', 'license' => 'allrightsreserved',
            ],
            'Existing teaching notes'
        );
        $sourcefiles = $fs->get_area_files(
            \context_module::instance($sfolder->cmid)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        );
        $saved = \local_filebank\local\migration\folder_destination::copy(reset($sourcefiles), $tfolder->cmid, false);
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
        [, , , $sfolder, $tfolder] = $this->fixture();
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
            \local_filebank\local\migration\folder_destination::copy($file, $tfolder->cmid, false);
            $this->fail('The adapter must refuse a strict copy.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('lockedunsupported', $exception->errorcode);
        }
        $this->assertSame($draftcount, $DB->count_records('files', ['component' => 'user', 'filearea' => 'draft']));
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
        $rules->resolve(
            (object)['idnumber' => '550e8400-e29b-41d4-a716-446655440000||MAT101||2025',
                'shortname' => 'another-family']
        );
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
     * Unauthorized users cannot enumerate teacher materials.
     */
    public function test_catalog_requires_target_authority(): void {

        [$profileid, , $target] = $this->fixture();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        catalog::search($target->id, $profileid);
    }
}
