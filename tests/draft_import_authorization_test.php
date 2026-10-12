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
 * Authorization regression contracts.
 * @covers \local_filebank\local\selection\draft_import
 * @covers \local_filebank\local\selection\material_selection
 * @covers \local_filebank\local\profile\profiles
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\selection\draft_import::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\selection\material_selection::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
final class draft_import_authorization_test extends draft_import_fixture {
    /**
     * An administrator still cannot write a draft owned by another account.
     */
    public function test_foreign_draft_is_unavailable(): void {
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $actor = $this->getDataGenerator()->create_user();
        $this->setUser($actor);
        set_config('siteadmins', get_config('core', 'siteadmins') . ',' . $actor->id);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_filebank'));
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
        $this->expectExceptionMessage(get_string('invalidselection', 'local_filebank'));
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
        get_file_storage()->create_file_from_storedfile(
            [
                'contextid' => \context_module::instance($secondfolder->cmid)->id,
                'filepath' => '/copies/', 'filename' => 'copy.txt',
            ],
            $files[0]
        );
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
     * Long source names fit the native path field without separating a parent from ordinary subfolders.
     */
    public function test_long_paths_are_compacted_before_native_copy(): void {
        global $DB, $USER;
        [$profileid, $target, $folder, , $draftid] = $this->fixture();
        $DB->set_field('folder', 'name', str_repeat('Materials ', 24), ['id' => $folder->id]);
        $base = '/' . str_repeat('Destination ', 15) . '/';
        get_file_storage()->create_directory(\context_user::instance($USER->id)->id, 'user', 'draft', $draftid, $base);
        $items = [['cmid' => $folder->cmid, 'filepath' => '/']];
        $review = draft_import::review($target->id, $profileid, $draftid, $items, self::LIMITS, $base, 'folder');
        $this->assertCount(2, $review['operations']);
        foreach ($review['operations'] as $operation) {
            $this->assertLessThanOrEqual(255, \core_text::strlen($operation['filepath']));
            $this->assertStringStartsWith($base, $operation['filepath']);
        }
        $this->assertStringStartsWith($review['operations'][0]['filepath'], $review['operations'][1]['filepath']);
        draft_import::apply($target->id, $profileid, $draftid, $items, self::LIMITS, $review['digest'], $base, 'folder');
        foreach ($review['operations'] as $operation) {
            $file = get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $draftid,
                $operation['filepath'],
                $operation['filename']
            );
            $this->assertSame($operation['filename'], $file->get_content());
        }
    }
}
