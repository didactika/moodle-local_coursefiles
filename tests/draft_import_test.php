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
 * Native reviewed batch tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\selection\draft_import;
use local_filebank\local\selection\material_selection;
use local_filebank\local\profile\profiles;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/draft_import_fixture.php');

/**
 *  regression contracts.
 * @covers \local_filebank\local\selection\draft_import
 * @covers \local_filebank\local\selection\material_selection
 * @covers \local_filebank\local\profile\profiles
 */
final class draft_import_test extends draft_import_fixture {
    /**
     * Mixed overlapping selection preserves existing content and per-file declarations through native save.
     */
    public function test_mixed_selection_and_native_save(): void {
        global $DB, $USER;
        [$profileid, $target, $folder, $files, $draftid, $destination] = $this->fixture();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/'],
            ['cmid' => $folder->cmid, 'filepath' => '/notes/'], ['pathnamehash' => $files[0]->get_pathnamehash()]];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $this->assertCount(2, $review['operations']);
        $this->assertSame(3, $review['filecount']);
        $this->assertFalse($review['blocked']);
        $this->assertSame(0, $DB->count_records('local_filebank_author'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
        $context = \context_module::instance($destination->cmid);
        file_save_draft_area_files($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        $fs = get_file_storage();
        $this->assertSame('existing', $fs->get_file($context->id, 'mod_folder', 'content', 0, '/', 'existing.txt')->get_content());
        foreach ($files as $original) {
            $saved = $fs->get_file(
                $context->id,
                'mod_folder',
                'content',
                0,
                $original->get_filepath(),
                $original->get_filename()
            );
            $this->assertSame($original->get_content(), $saved->get_content());
            $this->assertSame($original->get_author(), $saved->get_author());
            $this->assertSame($original->get_license(), $saved->get_license());
            $uuid = json_decode($saved->get_source(), true)['filebank'];
            $record = $DB->get_record('local_filebank_author', ['uuid' => $uuid], '*', MUST_EXIST);
            $this->assertSame($original->get_author(), $record->author);
        }
        $this->assertSame(2, $DB->count_records('local_filebank_author'));
        $again = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $this->assertSame(['skip_identical', 'skip_identical'], array_column($again['operations'], 'decision'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $again['digest']);
        $this->assertSame(2, $DB->count_records('local_filebank_author'));
        $this->assertCount(
            3,
            $fs->get_area_files(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $draftid,
                'id',
                false
            )
        );
    }

    /**
     * Every destination restriction rejects the selection before writes.
     */
    public function test_control_limits_and_incomplete_contract(): void {
        global $DB, $USER;
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        foreach (
            [
            ['maxfiles' => 1], ['maxbytes' => 1], ['areamaxbytes' => 1],
            ['subdirs' => false], ['accepted_types' => ['.pdf']],
            ] as $override
        ) {
            try {
                draft_import::review($target->id, $profileid, $draftid, $items, array_replace(self::LIMITS, $override));
                $this->fail('A destination restriction must reject the batch.');
            } catch (\moodle_exception $exception) {
                $this->assertSame('batchlimit', $exception->errorcode);
            }
        }
        try {
            draft_import::review($target->id, $profileid, $draftid, $items, []);
            $this->fail('Missing limits cannot become unlimited.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchlimit', $exception->errorcode);
        }
        $this->assertSame(0, $DB->count_records('local_filebank_author'));
        $this->assertCount(
            1,
            get_file_storage()->get_area_files(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $draftid,
                'id',
                false
            )
        );
    }

    /**
     * A conflict prevents the whole reviewed batch, including earlier eligible files.
     */
    public function test_conflicting_author_blocks_entire_batch(): void {
        global $DB, $USER;
        [$profileid, $target, $folder, $files, $draftid] = $this->fixture();
        $this->preventResetByRollback();
        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        $fs->create_file_from_storedfile(
            ['contextid' => $context->id, 'component' => 'user', 'filearea' => 'draft',
                'itemid' => $draftid, 'filepath' => '/notes/', 'filename' => 'second.txt', 'author' => 'Other author'],
            $files[1]
        );
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $this->assertTrue($review['blocked']);
        $this->assertSame(['copy', 'blocked_attribution'], array_column($review['operations'], 'decision'));
        try {
            draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
            $this->fail('Conflicting declarations must reject all operations.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchconflict', $exception->errorcode);
        }
        $this->assertSame(0, $DB->count_records('local_filebank_author'));
        $this->assertFalse($fs->get_file($context->id, 'user', 'draft', $draftid, '/', 'first.txt'));
    }

    /**
     * Draft edits after review invalidate approval.
     */
    public function test_changed_draft_requires_new_review(): void {
        global $USER;
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $this->preventResetByRollback();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        get_file_storage()->create_file_from_string(
            ['contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
                'filepath' => '/', 'filename' => 'later.txt'],
            'later'
        );
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('batchconflict', 'local_filebank'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
    }

    /**
     * Activating another profile revision invalidates approval even if matching rules are unchanged.
     */
    public function test_changed_profile_requires_new_review(): void {
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $this->preventResetByRollback();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        [, $revision] = profiles::load($profileid);
        profiles::save($revision->configjson, $profileid);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('batchconflict', 'local_filebank'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
    }

    /**
     * Current strict policy is enforced again after review.
     */
    public function test_policy_change_blocks_apply(): void {
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $this->preventResetByRollback();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        set_config('author_policy', 'preserve_locked', 'local_filebank');
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('lockedunsupported', 'local_filebank'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
    }

    /**
     * A core failure after one successful addition rolls back all new files and declarations.
     */
    public function test_partial_core_failure_rolls_back_batch(): void {
        global $DB, $USER;
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $this->preventResetByRollback();
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $created = 0;
        if (!class_exists(\core_files\hook\after_file_created::class)) {
            $this->markTestSkipped('Core file-creation fault injection hooks are unavailable on this Moodle version.');
        }
        $manager = \core\di::get(\core\hook\manager::class);
        $manager->phpunit_redirect_hook(
            \core_files\hook\after_file_created::class,
            function ($hook) use (&$created): void {
                if (!$hook->storedfile->is_directory() && ++$created === 2) {
                    throw new \RuntimeException('Injected core failure');
                }
            }
        );
        try {
            draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
            $this->fail('A failure during the second copy must roll back the batch.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected core failure', $exception->getMessage());
        } finally {
            $manager->phpunit_stop_redirections();
        }
        $this->assertSame(2, $created);
        $this->assertSame(0, $DB->count_records('local_filebank_author'));
        $remaining = get_file_storage()->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        );
        $this->assertCount(1, $remaining);
        $this->assertSame('existing', reset($remaining)->get_content());
    }

    /**
     * Hidden modules cannot be included by selecting their containing folder.
     */
    public function test_hidden_folder_is_unavailable(): void {
        global $DB;
        [$profileid, $target, $folder] = $this->fixture();
        $DB->set_field('course_modules', 'visible', 0, ['id' => $folder->cmid]);
        rebuild_course_cache($folder->course, true);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('materialunavailable', 'local_filebank'));
        material_selection::resolve($target->id, $profileid, [['cmid' => $folder->cmid, 'filepath' => '/']]);
    }
}
