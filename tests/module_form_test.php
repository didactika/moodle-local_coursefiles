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
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge;

use local_coursebridge\local\catalog\indexer;
use local_coursebridge\local\catalog\material_source;
use local_coursebridge\local\profile\profiles;
use local_coursebridge\local\selection\selection_options;

/**
 * Exercises actual Folder and File settings forms and their native plugin callbacks.
 */
final class module_form_test extends \advanced_testcase {
    // PHPUnit 9 requires this isolation tag; PHPUnit 11 uses the equivalent attributes below.
    // phpcs:disable moodle.Commenting.ValidTags.Invalid
    /**
     * Native File settings retain their main file when importing another activity's main file.
     *
     * Isolate Moodle's request-wide no-submit cache from other simulated requests.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    // phpcs:enable moodle.Commenting.ValidTags.Invalid
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_resource_form_preserves_destination_main_file(): void {
        global $USER, $DB;
        [$course, $cm, $profileid, $sourcefolder, $resource] = $this->fixture('resource');
        $resource->set_sortorder(1);
        $html = $this->request($course, $cm)->render();
        $this->assertStringContainsString('Set main file', $html);
        $data = $this->hidden($html);
        $selected = ['folder:' . $sourcefolder->cmid . ':' . base64_encode('/'), 'file:' . $resource->get_pathnamehash()];
        $reviewdata = $this->hidden($this->request($course, $cm, array_replace($data, [
            'cbitems' => $selected, 'cbreview' => 'Review selection', 'cbprofile' => $profileid,
        ]))->render());
        $this->assertNotEmpty($reviewdata['cbdigest']);
        $applyform = $this->request($course, $cm, array_replace($reviewdata, [
            'name' => 'Pending resource title', 'cbitems' => $selected, 'cbapply' => 'Add reviewed materials',
            'cbprofile' => $profileid,
        ]));
        $this->assertStringContainsString('Materials added to the draft', $applyform->render());
        $fs = get_file_storage();
        $files = $fs->get_area_files(\context_user::instance($USER->id)->id, 'user', 'draft', (int)$data['files'], 'id', false);
        $this->assertCount(4, $files);
        foreach ($files as $file) {
            $this->assertSame($file->get_filename() === 'existing.txt' ? 1 : 0, (int)$file->get_sortorder());
        }
        $this->assertSame(1, (int)$resource->get_sortorder());
        $this->assertCount(1, $fs->get_area_files(
            \context_module::instance($cm->id)->id,
            'mod_resource',
            'content',
            0,
            'id',
            false
        ));
        $this->assertSame('Target materials', $DB->get_field('resource', 'name', ['id' => $cm->instance]));
        $saveform = $this->request($course, $cm, array_replace($this->hidden($applyform->render()), [
            'name' => 'Saved resource title', 'submitbutton' => 'Save and display',
            'availabilityconditionsjson' => '', 'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0],
        ]));
        $savedata = $saveform->get_data();
        $this->assertNotNull($savedata);
        update_moduleinfo($cm, $savedata, $course, $saveform);
        $savedfiles = $fs->get_area_files(\context_module::instance($cm->id)->id, 'mod_resource', 'content', 0, 'id', false);
        $this->assertCount(4, $savedfiles);
        foreach ($savedfiles as $file) {
            $this->assertSame($file->get_filename() === 'existing.txt' ? 1 : 0, (int)$file->get_sortorder());
        }
        $this->assertSame('Saved resource title', $DB->get_field('resource', 'name', ['id' => $cm->instance]));
    }

    /**
     * Real native fields use their actual contract and mixed choices fill only the current draft.
     */
    public function test_review_and_apply_in_actual_folder_form(): void {
        global $USER, $DB;
        [$course, $cm, $profileid, $sourcefolder, $resource] = $this->fixture();
        $form = $this->request($course, $cm);
        $html = $form->render();
        $this->assertStringContainsString('Reuse related materials', $html);
        $this->assertStringContainsString('Folder: SOURCE2025: Source materials/', $html);
        $this->assertStringContainsString('File: SOURCE2025: Extra resource/extra.txt', $html);
        $data = $this->hidden($html);
        $draftid = (int)$data['files'];
        $selected = ['folder:' . $sourcefolder->cmid . ':' . base64_encode('/'), 'file:' . $resource->get_pathnamehash()];
        $reviewform = $this->request($course, $cm, array_replace($data, [
            'name' => 'Pending folder title', 'cbitems' => $selected, 'cbreview' => 'Review selection', 'cbprofile' => $profileid,
        ]));
        $reviewhtml = $reviewform->render();
        $this->assertStringContainsString('first.txt', $reviewhtml);
        $this->assertStringContainsString('second.txt', $reviewhtml);
        $this->assertStringContainsString('extra.txt', $reviewhtml);
        $this->assertSame(0, $DB->count_records('local_coursebridge_attribution'));
        $reviewdata = $this->hidden($reviewhtml);
        $this->assertNotEmpty($reviewdata['cbdigest']);
        $applyform = $this->request($course, $cm, array_replace($reviewdata, [
            'name' => 'Pending folder title', 'cbitems' => $selected, 'cbapply' => 'Add reviewed materials',
            'cbprofile' => $profileid,
        ]));
        $applyhtml = $applyform->render();
        $this->assertStringContainsString('Materials added to the draft', $applyhtml);
        $this->assertStringContainsString('value="Pending folder title"', $applyhtml);
        $fs = get_file_storage();
        $this->assertCount(4, $fs->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        ));
        $this->assertCount(1, $fs->get_area_files(
            \context_module::instance($cm->id)->id,
            'mod_folder',
            'content',
            0,
            'id',
            false
        ));
        $this->assertSame('Target materials', $DB->get_field('folder', 'name', ['id' => $cm->instance]));
        $this->assertSame(3, $DB->count_records('local_coursebridge_attribution'));
        $this->assertSame('', $this->hidden($applyhtml)['cbdigest']);
    }

    /**
     * Changing visible selection after review cannot apply the earlier hidden approval.
     */
    public function test_changed_selection_cannot_apply_old_review(): void {
        global $DB;
        [$course, $cm, $profileid, $sourcefolder, $resource] = $this->fixture();
        $data = $this->hidden($this->request($course, $cm)->render());
        $selected = ['folder:' . $sourcefolder->cmid . ':' . base64_encode('/')];
        $reviewdata = $this->hidden($this->request($course, $cm, array_replace($data, [
            'cbitems' => $selected, 'cbreview' => 'Review selection', 'cbprofile' => $profileid,
        ]))->render());
        $html = $this->request($course, $cm, array_replace($reviewdata, [
            'cbitems' => ['file:' . $resource->get_pathnamehash()], 'cbapply' => 'Add reviewed materials',
            'cbprofile' => $profileid,
        ]))->render();
        $this->assertStringContainsString('changed after review', $html);
        $this->assertSame(0, $DB->count_records('local_coursebridge_attribution'));
    }

    /**
     * Group repeated file content while retaining explicit folder edition choices and off-page selection.
     */
    public function test_choice_grouping_and_retained_selection(): void {
        global $USER;
        [$course, $cm, $profileid, $sourcefolder, $resource] = $this->fixture();
        $teacher = $USER;
        $this->setAdminUser();
        $duplicate = $this->getDataGenerator()->create_module('folder', [
            'course' => $sourcefolder->course, 'name' => 'Other source',
        ]);
        get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($duplicate->cmid)->id, 'component' => 'mod_folder',
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'first.txt',
            'author' => 'Author first.txt', 'license' => 'cc',
        ], 'first.txt');
        material_source::publish($profileid, $duplicate->cmid, false);
        indexer::refresh($sourcefolder->course);
        $this->setUser($teacher);
        $selected = ['file:' . $resource->get_pathnamehash()];
        $page = selection_options::page($course->id, $profileid, $cm->id, ['q' => 'first'], '', $selected);
        $filechoices = array_filter(array_keys($page['choices']), static fn($value) => str_starts_with($value, 'file:'));
        $this->assertCount(2, $filechoices);
        $this->assertSame('Selected item 1 (outside current results)', $page['choices'][$selected[0]]);
        $this->assertArrayHasKey('folder:' . $sourcefolder->cmid . ':' . base64_encode('/'), $page['choices']);
        $this->assertArrayHasKey('folder:' . $duplicate->cmid . ':' . base64_encode('/'), $page['choices']);
        $all = selection_options::page($course->id, $profileid, $cm->id, [], '', []);
        $keys = array_keys($all['choices']);
        $this->assertLessThan(
            array_search('folder:' . $sourcefolder->cmid . ':' . base64_encode('/notes/'), $keys),
            array_search('folder:' . $sourcefolder->cmid . ':' . base64_encode('/'), $keys)
        );
        foreach (
            [['file:bad'], [['nested']], ['folder:999999999999999999999:' . base64_encode('/')],
            ['folder:1:' . base64_encode('/../')]] as $invalid
        ) {
            try {
                selection_options::parse($invalid);
                $this->fail('Malformed selection values must be rejected.');
            } catch (\moodle_exception $exception) {
                $this->assertSame('invalidselection', $exception->errorcode);
            }
        }
    }

    /**
     * An empty review explains the problem and leaves the native selector usable.
     */
    public function test_empty_review_retains_available_choices(): void {
        [$course, $cm, $profileid] = $this->fixture();
        $data = $this->hidden($this->request($course, $cm)->render());
        $html = $this->request($course, $cm, array_replace($data, [
            'cbitems' => [], 'cbreview' => 'Review selection', 'cbprofile' => $profileid,
        ]))->render();
        $this->assertStringContainsString('Select available files or non-empty folders', $html);
        $this->assertStringContainsString('Folder: SOURCE2025: Source materials/', $html);
        $this->assertStringContainsString('File: SOURCE2025: Extra resource/extra.txt', $html);
        $this->assertSame('', $this->hidden($html)['cbdigest']);
    }

    /**
     * Source names cannot introduce markup into native autocomplete options.
     */
    public function test_source_labels_are_escaped_in_native_options(): void {
        global $DB, $USER;
        [$course, $cm, , $sourcefolder] = $this->fixture();
        $teacher = $USER;
        $this->setAdminUser();
        $name = 'Source <img src=x onerror=alert(1)>';
        $DB->set_field('folder', 'name', $name, ['id' => $sourcefolder->id]);
        rebuild_course_cache($sourcefolder->course, true);
        indexer::refresh($sourcefolder->course);
        $this->setUser($teacher);
        $html = $this->request($course, $cm)->render();
        $this->assertStringContainsString(s($name), $html);
        $this->assertStringNotContainsString($name, $html);
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
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/folder/mod_form.php');
        require_once($CFG->dirroot . '/mod/resource/mod_form.php');
        $config = json_decode(file_get_contents($CFG->dirroot . '/local/coursebridge/examples/institutional-profile.json'), true);
        $config['publication']['mode'] = 'explicit-family-publication';
        $profileid = profiles::save(json_encode($config));
        set_config('author_policy', 'editable_with_provenance', 'local_coursebridge');
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
        $PAGE->set_context(\context_module::instance($cm->id));
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
