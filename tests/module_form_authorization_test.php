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
 * Authorization regression contracts.
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\selection\selection_options
 */
final class module_form_authorization_test extends module_form_fixture {
    /**
     * Folder-only teachers can reuse files without permission to change activity settings.
     */
    public function test_restricted_folder_content_editor_can_import(): void {

        global $CFG, $PAGE, $USER;
        [, $cm, $profileid, $source] = $this->fixture();
        $context = \context_module::instance($cm->id);
        $roles = get_archetype_roles('editingteacher');
        $role = (int)reset($roles)->id;
        assign_capability('moodle/course:manageactivities', CAP_PROHIBIT, $role, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        $this->assertFalse(has_capability('moodle/course:manageactivities', $context));
        $this->assertTrue(has_capability('mod/folder:managefiles', $context));
        require_once($CFG->dirroot . '/mod/folder/edit_form.php');
        $PAGE->set_url('/mod/folder/edit.php', ['id' => $cm->id]);
        $options = ['subdirs' => true, 'maxbytes' => 0, 'maxfiles' => -1, 'accepted_types' => '*', 'context' => $context];
        $data = file_prepare_standard_filemanager(
            (object)['id' => $cm->id],
            'files',
            $options,
            $context,
            'mod_folder',
            'content',
            0
        );
        $form = new \mod_folder_edit_form(null, ['data' => $data, 'options' => $options]);
        $form->render();
        $control = \local_filebank\fixture\native_form::control($form, 'files_filemanager');
        $token = \local_filebank\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $this->assertNotEmpty($token);
        $review = \local_filebank\local\selection\draft_state::review(
            $token,
            $profileid,
            [['cmid' => $source->cmid, 'filepath' => '/']],
            sesskey()
        );
        $this->assertFalse($review['blocked']);
        \local_filebank\local\selection\draft_state::apply($token, $review['digest'], sesskey());
        $this->assertNotFalse(
            get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                (int)$control->getValue(),
                '/',
                'first.txt'
            )
        );
    }
}
