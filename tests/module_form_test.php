<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker;

use local_multipicker\local\catalog\indexer;
use local_multipicker\local\catalog\material_source;
use local_multipicker\local\profile\profiles;
use local_multipicker\local\selection\selection_options;

/**
 * Exercises native controls, browser selections and restricted folder editing.
 * @covers \local_multipicker\local\catalog\indexer
 * @covers \local_multipicker\local\catalog\material_source
 * @covers \local_multipicker\local\profile\profiles
 * @covers \local_multipicker\local\selection\selection_options
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\catalog\material_source::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\profile\profiles::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\local\selection\selection_options::class)]
final class module_form_test extends \advanced_testcase {
    /**
     * Native settings render one real manager without extra settings sections.
     */
    public function test_native_control_binds_actual_draft(): void {
        [$course, $cm] = $this->fixture();
        $form = $this->request($course, $cm);
        $html = $form->render();
        $this->assertStringNotContainsString('cbheader', $html);
        $this->assertStringNotContainsString('cbitems', $html);
        $control = \local_multipicker\fixture\native_form::control($form, 'files');
        $this->assertInstanceOf(\local_multipicker\form\native_filemanager::class, $control);
        $context = \context_module::instance($cm->id);
        $token = \local_multipicker\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $this->assertNotEmpty($token);
        $scope = \local_multipicker\local\selection\draft_scope::describe($token, sesskey());
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
        $control = new \local_multipicker\form\native_filemanager(
            'files',
            'Files',
            null,
            ['subdirs' => true, 'maxfiles' => -1, 'accepted_types' => '*']
        );
        $control->updateAttributes(['id' => 'id_files']);
        $html = $control->toHtml();
        $this->assertStringContainsString('name="files"', $html);
        $this->assertNotEmpty(\local_multipicker\local\selection\draft_scope::find(
            $context,
            (int)$control->getValue(),
            sesskey()
        ));
        $this->assertNotFalse(get_file_storage()->get_file(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$control->getValue(),
            '/',
            '.'
        ));
    }

    /**
     * Folder-only teachers can reuse files without permission to change activity settings.
     */
    public function test_restricted_folder_content_editor_can_import(): void {
        global $CFG, $PAGE, $USER;
        [$course, $cm, $profileid, $source] = $this->fixture();
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
        $control = \local_multipicker\fixture\native_form::control($form, 'files_filemanager');
        $token = \local_multipicker\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $this->assertNotEmpty($token);
        $review = \local_multipicker\local\selection\draft_scope::review(
            $token,
            $profileid,
            [['cmid' => $source->cmid, 'filepath' => '/']],
            sesskey()
        );
        $this->assertFalse($review['blocked']);
        \local_multipicker\local\selection\draft_scope::apply($token, $review['digest'], sesskey());
        $this->assertNotFalse(get_file_storage()->get_file(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$control->getValue(),
            '/',
            'first.txt'
        ));
    }

    /**
     * Browser navigation retains mixed choices and excludes destination activity content.
     */
    public function test_browser_shows_folders_then_files_and_retains_selection(): void {
        [$course, $cm, $profileid, $source, $resource] = $this->fixture();
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_multipicker\fixture\native_form::control($form, 'files');
        $token = \local_multipicker\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$control->getValue(),
            sesskey()
        );
        $scope = \local_multipicker\local\selection\draft_scope::describe($token, sesskey());
        $state = ['profileid' => $profileid, 'selected' => ['file:' . $resource->get_pathnamehash()],
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => '', 'mimetype' => ''],
            'after' => '', 'folder' => ''];
        $page = \local_multipicker\local\selection\browser_listing::page($scope, $state);
        $this->assertContains('Source materials', array_column($page['cards'], 'title'));
        $this->assertNotContains('existing.txt', array_column($page['cards'], 'title'));
        $state['folder'] = 'folder:' . $source->cmid . ':' . base64_encode('/');
        $page = \local_multipicker\local\selection\browser_listing::page($scope, $state);
        $this->assertContains('first.txt', array_column($page['cards'], 'title'));
        $this->assertContains('notes', array_column($page['cards'], 'title'));
        $this->assertSame([['value' => 'file:' . $resource->get_pathnamehash()]], $page['retained']);
    }

    /**
     * Folder mapping is applied to the draft and changing it consumes the old approval.
     */
    public function test_folder_mode_copies_and_invalidates_old_review(): void {
        global $USER;
        [$course, $cm, $profileid, $source] = $this->fixture();
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_multipicker\fixture\native_form::control($form, 'files');
        $token = \local_multipicker\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$control->getValue(),
            sesskey()
        );
        $items = [['cmid' => $source->cmid, 'filepath' => '/']];
        \local_multipicker\local\selection\draft_scope::select_mode($token, 'folder', sesskey());
        $review = \local_multipicker\local\selection\draft_scope::review($token, $profileid, $items, sesskey());
        \local_multipicker\local\selection\draft_scope::select_mode($token, 'contents', sesskey());
        try {
            \local_multipicker\local\selection\draft_scope::approved($token, $review['digest'], sesskey());
            $this->fail('Changing folder structure must invalidate approval.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchconflict', $exception->errorcode);
        }
        \local_multipicker\local\selection\draft_scope::select_mode($token, 'folder', sesskey());
        $review = \local_multipicker\local\selection\draft_scope::review($token, $profileid, $items, sesskey());
        \local_multipicker\local\selection\draft_scope::apply($token, $review['digest'], sesskey());
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
        global $USER;
        [$course, $cm, $profileid, $source, $resource] = $this->fixture('resource');
        $resource->set_sortorder(1);
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_multipicker\fixture\native_form::control($form, 'files');
        $context = \context_module::instance($cm->id);
        $token = \local_multipicker\local\selection\draft_scope::find($context, (int)$control->getValue(), sesskey());
        $review = \local_multipicker\local\selection\draft_scope::review(
            $token,
            $profileid,
            [['cmid' => $source->cmid, 'filepath' => '/'], ['pathnamehash' => $resource->get_pathnamehash()]],
            sesskey()
        );
        \local_multipicker\local\selection\draft_scope::apply($token, $review['digest'], sesskey());
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
        $files = \local_multipicker\local\selection\material_selection::resolve($course->id, $profileid, $items);
        $this->assertCount(2, $files);
        $mapped = \local_multipicker\local\selection\material_selection::map_paths($files, $items, 'folder');
        $this->assertContains('/Source materials/notes/', $mapped);
        $mapped = \local_multipicker\local\selection\material_selection::map_paths($files, [$items[0]], 'contents');
        $this->assertSame(['/', '/'], array_values($mapped));
    }

    /**
     * Create current source files, publication and a destination-only teacher.
     *
     * @param string $modulename Destination module type.
     * @return array Destination course/module, profile, source folder and resource file.
     */
    private function fixture(string $modulename = 'folder'): array {
        global $CFG, $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once(__DIR__ . '/fixtures/native_form.php');
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/folder/mod_form.php');
        require_once($CFG->dirroot . '/mod/resource/mod_form.php');
        $config = json_decode(file_get_contents($CFG->dirroot . '/local/multipicker/examples/institutional-profile.json'), true);
        $config['publication']['mode'] = 'explicit-family-publication';
        $profileid = profiles::save(json_encode($config));
        set_config('author_policy', 'editable_with_provenance', 'local_multipicker');
        $uuid = \core\uuid::generate();
        $source = $this->getDataGenerator()->create_course(['shortname' => 'SOURCE2025', 'idnumber' => $uuid . '||CB||2025']);
        $target = $this->getDataGenerator()->create_course(['shortname' => 'TARGET2026', 'idnumber' => $uuid . '||CB||2026']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id, 'name' => 'Source materials']);
        $fs = get_file_storage();
        foreach ([['/', 'first.txt'], ['/notes/', 'second.txt']] as $file) {
            $fs->create_file_from_string(['contextid' => \context_module::instance($folder->cmid)->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                'filepath' => $file[0], 'filename' => $file[1], 'author' => 'Author ' . $file[1], 'license' => 'cc'], $file[1]);
        }
        $resourcemodule = $this->getDataGenerator()->create_module('resource', [
            'course' => $source->id, 'name' => 'Extra resource',
        ]);
        $resource = $fs->create_file_from_string(['contextid' => \context_module::instance($resourcemodule->cmid)->id,
            'component' => 'mod_resource', 'filearea' => 'content', 'itemid' => 0,
            'filepath' => '/', 'filename' => 'extra.txt', 'author' => 'Resource author', 'license' => 'cc'], 'extra');
        material_source::publish($profileid, $folder->cmid, false);
        material_source::publish($profileid, $resourcemodule->cmid, false);
        $destination = $this->getDataGenerator()->create_module($modulename, [
            'course' => $target->id, 'name' => 'Target materials',
        ]);
        $cm = get_coursemodule_from_id($modulename, $destination->cmid, $target->id, false, MUST_EXIST);
        // The resource test generator creates a sample file; establish an explicit one-file fixture instead.
        $fs->delete_area_files(\context_module::instance($cm->id)->id, 'mod_' . $modulename, 'content', 0);
        $fs->create_file_from_string(['contextid' => \context_module::instance($cm->id)->id,
            'component' => 'mod_' . $modulename, 'filearea' => 'content', 'itemid' => 0,
            'filepath' => '/', 'filename' => 'existing.txt', 'author' => 'Existing author', 'license' => 'cc',
            'sortorder' => $modulename === 'resource' ? 1 : 0], 'existing');
        indexer::refresh($source->id);
        indexer::refresh($target->id);
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $target->id, 'editingteacher');
        $this->setUser($teacher);
        $PAGE->set_course($target);
        $PAGE->set_cm($cm, $target);
        \local_multipicker\form\native_filemanager::register_native(new \core\hook\after_config());
        $PAGE->set_url('/course/modedit.php', ['update' => $cm->id]);
        return [$target, $cm, $profileid, $folder, $resource];
    }

    /**
     * Build the same real module form as the native settings controller.
     *
     * @param \stdClass $course Destination course.
     * @param \stdClass $cm Destination module.
     * @param array $submission Optional request values.
     * @return \moodleform_mod Actual form.
     */
    private function request(\stdClass $course, \stdClass $cm, array $submission = []): \moodleform_mod {
        $_POST = [];
        $classname = '\\mod_' . $cm->modname . '_mod_form';
        if ($submission) {
            $classname::mock_submit($submission);
        }
        [$cm, , , $data, $section] = get_moduleinfo_data($cm, $course);
        $form = new $classname($data, $section->section, $cm, $course);
        $form->set_data($data);
        return $form;
    }

    /**
     * Extract native hidden values without relying on internal QuickForm state.
     *
     * @param string $html Actual rendered form.
     * @return array Hidden fields.
     */
    private function hidden(string $html): array {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $values = [];
        foreach ($xpath->query('//input[@type="hidden"]') as $input) {
            $values[$input->getAttribute('name')] = $input->getAttribute('value');
        }
        return $values;
    }
}
