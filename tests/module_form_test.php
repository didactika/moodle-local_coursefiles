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
 *  regression contracts.
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\selection\selection_options
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\material_source::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\selection\selection_options::class)]
final class module_form_test extends module_form_fixture {
    /**
     * Native settings render one real manager without extra settings sections.
     */
    public function test_native_control_binds_actual_draft(): void {
        [$course, $cm] = $this->fixture();
        $form = $this->request($course, $cm);
        $html = $form->render();
        $this->assertStringNotContainsString('cbheader', $html);
        $this->assertStringNotContainsString('cbitems', $html);
        $control = \local_filebank\fixture\native_form::control($form, 'files');
        $this->assertInstanceOf(\local_filebank\form\native_filemanager::class, $control);
        $context = \context_module::instance($cm->id);
        $token = \local_filebank\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $this->assertNotEmpty($token);
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $this->assertSame((int)$this->hidden($html)['files'], $scope['draftid']);
        $this->assertSame('files', $scope['field']);
    }

    /**
     * An unsaved activity receives its own empty draft before opening the browser.
     */
    public function test_new_activity_control_uses_native_draft(): void {
        global $PAGE, $USER;
        [$course] = $this->fixture();
        $PAGE = new \moodle_page();
        $PAGE->set_course($course);
        $context = \context_course::instance($course->id);
        $PAGE->set_context($context);
        $PAGE->set_url('/course/modedit.php', ['add' => 'folder', 'course' => $course->id]);
        $control = new \local_filebank\form\native_filemanager(
            'files',
            'Files',
            null,
            ['subdirs' => true, 'maxfiles' => -1, 'accepted_types' => '*']
        );
        $control->updateAttributes(['id' => 'id_files']);
        $html = $control->toHtml();
        $this->assertStringContainsString('name="files"', $html);
        $this->assertNotEmpty(
            \local_filebank\local\selection\draft_scope::find(
                $context,
                (int)$control->getValue(),
                sesskey()
            )
        );
        $this->assertNotFalse(
            get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                (int)$control->getValue(),
                '/',
                '.'
            )
        );
    }
}
