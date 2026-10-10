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
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefiles;

use local_coursefiles\local\selection\draft_import;
use local_coursefiles\local\selection\material_selection;
use local_coursefiles\local\profile\profiles;

/**
 * Exercises real core drafts and current source permissions.
 * @covers \local_coursefiles\local\selection\draft_import
 * @covers \local_coursefiles\local\selection\material_selection
 * @covers \local_coursefiles\local\profile\profiles
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\selection\draft_import::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\selection\material_selection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\profile\profiles::class)]
final class draft_import_test extends \advanced_testcase {
    /**
     * @var array Authoritative fixture form options.
     */
    private const LIMITS = ['maxfiles' => 20, 'maxbytes' => 1024, 'areamaxbytes' => 10240,
        'subdirs' => true, 'accepted_types' => ['*']];

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
        $this->assertSame(0, $DB->count_records('local_coursefiles_author'));
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
            $uuid = json_decode($saved->get_source(), true)['coursefiles'];
            $record = $DB->get_record('local_coursefiles_author', ['uuid' => $uuid], '*', MUST_EXIST);
            $this->assertSame($original->get_author(), $record->author);
        }
        $this->assertSame(2, $DB->count_records('local_coursefiles_author'));
        $again = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $this->assertSame(['skip_identical', 'skip_identical'], array_column($again['operations'], 'decision'));
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $again['digest']);
        $this->assertSame(2, $DB->count_records('local_coursefiles_author'));
        $this->assertCount(3, $fs->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        ));
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
        $this->assertSame(0, $DB->count_records('local_coursefiles_author'));
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        ));
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
        $fs->create_file_from_storedfile(['contextid' => $context->id, 'component' => 'user', 'filearea' => 'draft',
            'itemid' => $draftid, 'filepath' => '/notes/', 'filename' => 'second.txt', 'author' => 'Other author'], $files[1]);
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
        $this->assertSame(0, $DB->count_records('local_coursefiles_author'));
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
        get_file_storage()->create_file_from_string(['contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
            'filepath' => '/', 'filename' => 'later.txt'], 'later');
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('batchconflict', 'local_coursefiles'));
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
        $this->expectExceptionMessage(get_string('batchconflict', 'local_coursefiles'));
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
        set_config('author_policy', 'preserve_locked', 'local_coursefiles');
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('lockedunsupported', 'local_coursefiles'));
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
        $manager->phpunit_redirect_hook(\core_files\hook\after_file_created::class, function ($hook) use (&$created): void {
            if (!$hook->storedfile->is_directory() && ++$created === 2) {
                throw new \RuntimeException('Injected core failure');
            }
        });
        try {
            draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
            $this->fail('A failure during the second copy must roll back the batch.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected core failure', $exception->getMessage());
        } finally {
            $manager->phpunit_stop_redirections();
        }
        $this->assertSame(2, $created);
        $this->assertSame(0, $DB->count_records('local_coursefiles_author'));
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
        $this->expectExceptionMessage(get_string('materialunavailable', 'local_coursefiles'));
        material_selection::resolve($target->id, $profileid, [['cmid' => $folder->cmid, 'filepath' => '/']]);
    }

    /**
     * An administrator still cannot write a draft owned by another account.
     */
    public function test_foreign_draft_is_unavailable(): void {
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $actor = $this->getDataGenerator()->create_user();
        $this->setUser($actor);
        set_config('siteadmins', get_config('core', 'siteadmins') . ',' . $actor->id);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_coursefiles'));
        draft_import::review(
            $target->id,
            $profileid,
            $draftid,
            [['cmid' => $folder->cmid, 'filepath' => '/']],
            self::LIMITS
        );
    }

    /**
     * SQL wildcard characters select literal paths and do not include neighbouring directories.
     */
    public function test_literal_folder_paths_and_selection_bound(): void {
        [$profileid, $target, $folder, $files] = $this->fixture();
        $fs = get_file_storage();
        foreach (['/unit_%/', '/unit_ab/'] as $path) {
            $fs->create_file_from_storedfile(['filepath' => $path, 'filename' => 'sample.txt'], $files[0]);
        }
        $selected = material_selection::resolve(
            $target->id,
            $profileid,
            [['cmid' => $folder->cmid, 'filepath' => '/unit_%/']]
        );
        $this->assertCount(1, $selected);
        $this->assertSame('/unit_%/', reset($selected)->get_filepath());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidselection', 'local_coursefiles'));
        material_selection::resolve(
            $target->id,
            $profileid,
            array_fill(0, 201, ['pathnamehash' => $files[0]->get_pathnamehash()])
        );
    }

    /**
     * Several source folders share one batch and configured identical content is skipped across paths.
     */
    public function test_multiple_folders_skip_duplicate_content(): void {
        [$profileid, $target, $folder, $files, $draftid] = $this->fixture();
        $secondfolder = $this->getDataGenerator()->create_module('folder', ['course' => $folder->course]);
        get_file_storage()->create_file_from_storedfile([
            'contextid' => \context_module::instance($secondfolder->cmid)->id,
            'filepath' => '/copies/', 'filename' => 'copy.txt',
        ], $files[0]);
        $items = [['cmid' => $folder->cmid, 'filepath' => '/'],
            ['cmid' => $secondfolder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS);
        $this->assertSame(['copy', 'copy', 'skip_content'], array_column($review['operations'], 'decision'));
        $this->assertSame(3, $review['filecount']);
        $this->assertFalse($review['blocked']);
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest']);
    }

    /**
     * Destination renames are reviewed, constrained and bound to approval.
     */
    public function test_reviewed_rename_preserves_author_and_rejects_changed_approval(): void {
        global $USER;
        [$profileid, $target, , $files, $draftid] = $this->fixture();
        $items = [['pathnamehash' => $files[0]->get_pathnamehash()]];
        $options = ['renames' => [$files[0]->get_pathnamehash() => 'renamed.txt']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS, '/', 'paths', $options);
        $this->assertSame('renamed.txt', $review['operations'][0]['filename']);
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest'], '/', 'paths', $options);
        $saved = get_file_storage()->get_file(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            '/',
            'renamed.txt'
        );
        $this->assertSame($files[0]->get_author(), $saved->get_author());
        $this->assertSame($files[0]->get_content(), $saved->get_content());
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS, '/', 'paths', $options);
        $options['renames'][$files[0]->get_pathnamehash()] = 'different.txt';
        $this->expectException(\moodle_exception::class);
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest'], '/', 'paths', $options);
    }

    /**
     * A renamed extension cannot bypass the native control's accepted types.
     */
    public function test_rename_cannot_bypass_accepted_types(): void {
        [$profileid, $target, , $files, $draftid] = $this->fixture();
        $this->expectException(\moodle_exception::class);
        draft_import::review(
            $target->id,
            $profileid,
            $draftid,
            [['pathnamehash' => $files[0]->get_pathnamehash()]],
            array_replace(self::LIMITS, ['accepted_types' => ['.txt']]),
            '/',
            'paths',
            ['renames' => [$files[0]->get_pathnamehash() => 'not-allowed.exe']]
        );
    }

    /**
     * Create real source folders and an existing native destination draft.
     *
     * @return array Profile, target, source module, files, draft and destination module.
     */
    private function fixture(): array {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/filelib.php');
        $profileid = profiles::save(file_get_contents($CFG->dirroot . '/local/coursefiles/examples/institutional-profile.json'));
        set_config('author_policy', 'editable_with_provenance', 'local_coursefiles');
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $source = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2025']);
        $target = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2026']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id]);
        $fs = get_file_storage();
        $files = [];
        $declarations = [['/', 'first.txt', 'First author', 'cc'],
            ['/notes/', 'second.txt', 'Second author', 'allrightsreserved']];
        foreach ($declarations as $item) {
            $files[] = $fs->create_file_from_string(['contextid' => \context_module::instance($folder->cmid)->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                'filepath' => $item[0], 'filename' => $item[1], 'author' => $item[2], 'license' => $item[3]], $item[1]);
        }
        $destination = $this->getDataGenerator()->create_module('folder', ['course' => $target->id]);
        $context = \context_module::instance($destination->cmid);
        $fs->create_file_from_string(['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
            'itemid' => 0, 'filepath' => '/', 'filename' => 'existing.txt', 'author' => 'Existing author'], 'existing');
        $draftid = 0;
        file_prepare_draft_area($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        return [$profileid, $target, $folder, $files, $draftid, $destination];
    }
}
