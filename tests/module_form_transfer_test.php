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
 * Real native activity form batch integration checks.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\catalog\indexer;
use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\selection\selection_options;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/module_form_fixture.php');

/**
 * Transfer regression contracts.
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\selection\selection_options
 */
final class module_form_transfer_test extends module_form_fixture {
    /**
     * Folder mapping is applied to the draft and changing it consumes the old approval.
     */
    public function test_folder_mode_copies_and_invalidates_old_review(): void {
        global $USER;
        [$course, $cm, $profileid, $source] = $this->fixture();
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_filebank\fixture\native_form::control($form, 'files');
        $token = \local_filebank\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$control->getValue(),
            sesskey()
        );
        $items = [['cmid' => $source->cmid, 'filepath' => '/']];
        \local_filebank\local\selection\draft_state::select_mode($token, 'folder', sesskey());
        $review = \local_filebank\local\selection\draft_state::review($token, $profileid, $items, sesskey());
        \local_filebank\local\selection\draft_state::select_mode($token, 'contents', sesskey());
        try {
            \local_filebank\local\selection\draft_scope::approved($token, $review['digest'], sesskey());
            $this->fail('Changing folder structure must invalidate approval.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchconflict', $exception->errorcode);
        }
        \local_filebank\local\selection\draft_state::select_mode($token, 'folder', sesskey());
        $review = \local_filebank\local\selection\draft_state::review($token, $profileid, $items, sesskey());
        \local_filebank\local\selection\draft_state::apply($token, $review['digest'], sesskey());
        $copied = get_file_storage()->get_file(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$control->getValue(),
            '/Source materials/notes/',
            'second.txt'
        );
        $this->assertNotFalse($copied);
        $this->assertSame('Author second.txt', $copied->get_author());
    }

    /**
     * A copied resource main-file marker cannot replace the destination main file.
     */
    public function test_resource_import_preserves_main_file(): void {

        [$course, $cm, $profileid, $source, $resource] = $this->fixture('resource');
        $resource->set_sortorder(1);
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_filebank\fixture\native_form::control($form, 'files');
        $context = \context_module::instance($cm->id);
        $token = \local_filebank\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $review = \local_filebank\local\selection\draft_state::review(
            $token,
            $profileid,
            [['cmid' => $source->cmid, 'filepath' => '/'], ['pathnamehash' => $resource->get_pathnamehash()]],
            sesskey()
        );
        \local_filebank\local\selection\draft_state::apply($token, $review['digest'], sesskey());
        file_save_draft_area_files((int)$control->getValue(), $context->id, 'mod_resource', 'content', 0, ['subdirs' => true]);
        $files = get_file_storage()->get_area_files($context->id, 'mod_resource', 'content', 0, 'id', false);
        $this->assertCount(4, $files);
        foreach ($files as $file) {
            $this->assertSame($file->get_filename() === 'existing.txt' ? 1 : 0, (int)$file->get_sortorder());
        }
        $this->assertSame(1, (int)$resource->get_sortorder());
    }

    /**
     * Copy modes retain or strip the selected root and overlapping selectors copy once.
     */
    public function test_folder_modes_and_overlap(): void {
        [$course, , $profileid, $source] = $this->fixture();
        $items = [['cmid' => $source->cmid, 'filepath' => '/notes/'],
            ['cmid' => $source->cmid, 'filepath' => '/']];
        $files = \local_filebank\local\selection\material_selection::resolve($course->id, $profileid, $items);
        $this->assertCount(2, $files);
        $mapped = \local_filebank\local\selection\material_selection::map_paths($files, $items, 'folder');
        $this->assertContains('/Source materials/notes/', $mapped);
        $mapped = \local_filebank\local\selection\material_selection::map_paths($files, [$items[0]], 'contents');
        $this->assertSame(['/', '/'], array_values($mapped));
    }
}
