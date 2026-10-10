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
 * CourseFiles native material import integration.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefiles;

use local_coursefiles\local\catalog\catalog;
use local_coursefiles\local\catalog\indexer;
use local_coursefiles\local\catalog\material_source;
use local_coursefiles\local\profile\profiles;

/**
 * Verify live public-area adapters and native control ownership on both supported cores.
 * @covers \local_coursefiles\local\catalog\catalog
 * @covers \local_coursefiles\local\catalog\indexer
 * @covers \local_coursefiles\local\catalog\material_source
 * @covers \local_coursefiles\local\profile\profiles
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\catalog\catalog::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\catalog\material_source::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\local\profile\profiles::class)]
final class public_sources_test extends \advanced_testcase {
    /**
     * Overlapping edition folders select each set of bytes once and synchronize their checks.
     */
    public function test_overlapping_folders_share_canonical_content_selection(): void {
        $fixture = $this->fixture();
        [$token] = $this->scope($fixture);
        $controller = \local_coursefiles\local\selection\browser_controller::class;
        $result = $controller::data($token, $controller::input([
            'profileid' => $fixture['profileid'], 'module' => 'folder', 'selectall' => true,
        ]));
        $this->assertSame('', $result['message']);
        $this->assertSame(3, $result['data']['count']);
        $scope = \local_coursefiles\local\selection\draft_scope::describe($token, sesskey());
        $records = \local_coursefiles\local\selection\selected_content::records($scope['view']['selected']);
        $this->assertCount(3, array_unique(array_column($records, 'contenthash')));
        foreach (json_decode($result['data']['folderstates'], true) as $state) {
            $this->assertTrue($state['checked']);
            $this->assertFalse($state['partial']);
        }
        $scope['view']['selected'] = array_column($result['data']['cards'], 'value');
        \local_coursefiles\local\selection\draft_scope::remember_view($token, $scope['view'], sesskey());
        $this->assertSame(3, $controller::data($token)['data']['count']);
    }

    /**
     * AJAX filters cannot render another native control's context or perform review actions.
     */
    public function test_fragment_rejects_an_unrelated_context(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/coursefiles/lib.php');
        [$token, $context] = $this->scope($this->fixture());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_coursefiles'));
        \local_coursefiles_output_fragment_browser(['scope' => $token,
            'context' => $context->get_course_context(), 'state' => '{}']);
    }

    /**
     * Off-page selections survive a live search; submitted review actions cannot authorize transfer.
     */
    public function test_fragment_keeps_selection_without_approving_transfer(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/coursefiles/lib.php');
        $fixture = $this->fixture();
        [$token, $context] = $this->scope($fixture);
        $state = \local_coursefiles\local\selection\browser_controller::data($token)['data'];
        $selected = $state['cards'][0]['value'];
        $html = \local_coursefiles_output_fragment_browser(['scope' => $token, 'context' => $context,
            'state' => json_encode(['profileid' => $fixture['profileid'], 'items' => [$selected],
                'q' => 'no-such-teaching-material', 'review' => true])]);
        $scope = \local_coursefiles\local\selection\draft_scope::describe($token, sesskey());
        $this->assertSame([$selected], $scope['view']['selected']);
        $this->assertArrayNotHasKey('review', $scope);
        $this->assertStringContainsString('data-coursefiles-panel', $html);
        $this->assertStringNotContainsString('data-coursefiles-confirm', $html);
    }


    /**
     * Select all includes authorized results beyond the first fifty-card page.
     */
    public function test_select_all_includes_other_result_pages(): void {
        global $DB;
        $fixture = $this->fixture();
        [$token] = $this->scope($fixture);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        for ($index = 0; $index < 61; $index++) {
            $this->file($folder, 'content', 'batch-match-' . $index . '.txt');
        }
        indexer::refresh($source->id);
        $result = \local_coursefiles\local\selection\browser_controller::data(
            $token,
            \local_coursefiles\local\selection\browser_controller::input([
                'profileid' => $fixture['profileid'], 'q' => 'batch-match-', 'selectall' => true,
            ])
        );
        $this->assertSame('', $result['message']);
        $this->assertSame(61, $result['data']['count']);
        $this->assertCount(50, $result['data']['cards']);
        $scope = \local_coursefiles\local\selection\draft_scope::describe($token, sesskey());
        $this->assertCount(61, $scope['view']['selected']);
    }

    /**
     * Toggling descendants and refreshing filters keep the current source directory.
     */
    public function test_fragment_keeps_folder_and_recursive_selection(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/coursefiles/lib.php');
        $fixture = $this->fixture();
        [$token, $context] = $this->scope($fixture);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        $value = 'folder:' . $folder->id . ':' . base64_encode('/');
        $input = ['profileid' => $fixture['profileid'], 'items' => [], 'toggleitem' => $value, 'openfolder' => $value];
        \local_coursefiles\local\selection\browser_controller::data(
            $token,
            \local_coursefiles\local\selection\browser_controller::input($input)
        );
        $scope = \local_coursefiles\local\selection\draft_scope::describe($token, sesskey());
        $this->assertCount(2, $scope['view']['selected']);
        $file = get_file_storage()->get_file($folder->context->id, 'mod_folder', 'content', 0, '/', 'lesson.txt');
        $html = \local_coursefiles_output_fragment_browser(['scope' => $token, 'context' => $context,
            'state' => json_encode(['profileid' => $fixture['profileid'], 'items' => $scope['view']['selected'],
                'toggleitem' => 'file:' . $file->get_pathnamehash()])]);
        $scope = \local_coursefiles\local\selection\draft_scope::describe($token, sesskey());
        $this->assertSame($value, $scope['view']['folder']);
        $this->assertCount(1, $scope['view']['selected']);
        $this->assertStringContainsString('coursefiles-back', $html);
    }

    /**
     * The embedded title uses the repository instance name rather than the component label.
     */
    public function test_browser_uses_repository_instance_name(): void {
        $fixture = $this->fixture();
        [$token] = $this->scope($fixture);
        \local_coursefiles\local\selection\draft_scope::select_repository($token, 'Course library', sesskey());
        $data = \local_coursefiles\local\selection\browser_controller::data($token)['data'];
        $this->assertSame('Course library', $data['repositoryname']);
    }

    /**
     * Assignment handouts are published, while student-owned components and undeclared areas are excluded.
     */
    public function test_assignment_handouts_exclude_private_files(): void {
        global $DB;
        $fixture = $this->fixture();
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        $modules = get_fast_modinfo($source)->get_instances_of('assign');
        $assignment = reset($modules);
        $result = catalog::search($fixture['targetcourse'], $fixture['profileid'], ['module' => 'assign']);
        $this->assertCount(1, $result['assets']);
        $this->assertSame('handout.txt', $result['assets'][0]['occurrences'][0]['filename']);
        $this->assertSame('introattachment', $result['assets'][0]['occurrences'][0]['filearea']);
        $private = get_file_storage()->get_file(
            $assignment->context->id,
            'assignsubmission_file',
            'submission_files',
            1,
            '/',
            'student-private.txt'
        );
        $this->expectException(\moodle_exception::class);
        material_source::resolve($private->get_pathnamehash(), $fixture['targetcourse'], $fixture['profileid']);
    }

    /**
     * Timed assignment instructions respect the exact core's public attachment rules.
     */
    public function test_assignment_timed_visibility_uses_core_rules(): void {
        global $DB, $CFG;
        $fixture = $this->fixture();
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        $modules = get_fast_modinfo($source)->get_instances_of('assign');
        $assignment = reset($modules);
        $DB->update_record('assign', (object)['id' => $assignment->instance, 'alwaysshowdescription' => 0,
            'allowsubmissionsfromdate' => time() + DAYSECS]);
        $this->file($assignment, 'activityattachment', 'future-instructions.txt');
        indexer::refresh($source->id);
        $result = catalog::search($fixture['targetcourse'], $fixture['profileid'], ['module' => 'assign']);
        $this->assertCount($CFG->version >= 2026100500 ? 1 : 0, $result['assets']);
        foreach ($result['assets'] as $asset) {
            $this->assertSame('handout.txt', $asset['occurrences'][0]['filename']);
        }
    }

    /**
     * Public module introductions are reusable without opening student discussion attachments.
     */
    public function test_generic_introduction_files_and_page_content(): void {
        global $DB;
        $fixture = $this->fixture();
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        foreach (['forum' => 'intro', 'page' => 'content'] as $module => $area) {
            $activity = $this->getDataGenerator()->create_module($module, ['course' => $source->id]);
            $cm = get_fast_modinfo($source)->get_cm($activity->cmid);
            $file = $this->file($cm, $area, $module . '-public.txt');
            material_source::publish($fixture['profileid'], $cm->id, false);
            indexer::refresh($source->id);
            $result = catalog::search($fixture['targetcourse'], $fixture['profileid'], ['module' => $module]);
            $this->assertCount(1, $result['assets']);
            $this->assertSame($file->get_pathnamehash(), $result['assets'][0]['occurrences'][0]['pathnamehash']);
        }
    }

    /**
     * Only visible book chapters belonging to the source book enter the catalog.
     */
    public function test_book_chapters_exclude_hidden_and_foreign_items(): void {
        global $DB;
        $fixture = $this->fixture();
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        $book = $this->getDataGenerator()->create_module('book', ['course' => $source->id]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $chapter = $generator->create_chapter(['bookid' => $book->id]);
        $hidden = $generator->create_chapter(['bookid' => $book->id, 'hidden' => 1]);
        $cm = get_fast_modinfo($source)->get_cm($book->cmid);
        $this->file($cm, 'chapter', 'visible-chapter.txt', $chapter->id);
        $this->file($cm, 'chapter', 'hidden-chapter.txt', $hidden->id);
        material_source::publish($fixture['profileid'], $cm->id, false);
        indexer::refresh($source->id);
        $result = catalog::search($fixture['targetcourse'], $fixture['profileid'], ['module' => 'book']);
        $this->assertCount(1, $result['assets']);
        $this->assertSame('visible-chapter.txt', $result['assets'][0]['occurrences'][0]['filename']);
    }

    /**
     * A URL activity's actual native editor is registered on edit, without an extra import button.
     */
    public function test_existing_url_editor_registers_native_scope(): void {
        global $CFG, $PAGE;
        $fixture = $this->fixture();
        $course = get_course($fixture['targetcourse']);
        $url = $this->getDataGenerator()->create_module('url', ['course' => $course->id,
            'externalurl' => 'https://example.org', 'intro' => 'Existing description', 'introformat' => FORMAT_HTML]);
        $cm = get_coursemodule_from_id('url', $url->cmid);
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/url/mod_form.php');
        $PAGE->set_cm($cm, $course);
        $PAGE->set_url('/course/modedit.php', ['update' => $cm->id]);
        \local_coursefiles\form\native_filemanager::register_native();
        [$cm, , , $data, $section] = get_moduleinfo_data($cm, $course);
        $form = new \mod_url_mod_form($data, $section->section, $cm, $course);
        $form->set_data($data);
        $html = $form->render();
        $this->assertStringNotContainsString('data-coursefiles-open', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $field = (new \DOMXPath($dom))->query('//input[@name="introeditor[itemid]"]')->item(0);
        $this->assertNotNull($field);
        $scope = \local_coursefiles\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$field->getAttribute('value'),
            sesskey()
        );
        $this->assertNotEmpty($scope);
        $contract = \local_coursefiles\local\selection\draft_scope::describe($scope, sesskey());
        $this->assertSame('url', $contract['module']);
        $this->assertSame('introeditor', $contract['field']);
        $this->assertSame('editor', $contract['environment']);
    }

    /**
     * An editor without a verified multi-link adapter retains its native individual behavior.
     */
    public function test_unadapted_editor_has_no_batch_scope(): void {
        global $CFG, $PAGE;
        $fixture = $this->fixture();
        $CFG->texteditors = 'textarea';
        $context = \context_module::instance($fixture['targetcmid']);
        $PAGE->set_context($context);
        $editor = new \local_coursefiles\form\native_editor(
            'introeditor',
            'Description',
            ['id' => 'id_introeditor'],
            ['context' => $context, 'maxfiles' => -1, 'subdirs' => true]
        );
        $draftid = file_get_unused_draft_itemid();
        $editor->setValue(['text' => 'Existing description', 'format' => FORMAT_HTML, 'itemid' => $draftid]);
        $editor->toHtml();
        $this->assertSame('', \local_coursefiles\local\selection\draft_scope::find($context, $draftid, sesskey()));
    }

    /**
     * An existing empty activity draft receives its native root and owning module filter.
     */
    public function test_existing_empty_filemanager_is_bound(): void {
        global $PAGE;
        $fixture = $this->fixture();
        $course = get_course($fixture['targetcourse']);
        $cm = get_coursemodule_from_id('folder', $fixture['targetcmid']);
        $PAGE->set_cm($cm, $course);
        $PAGE->set_url('/mod/folder/edit.php', ['id' => $cm->id]);
        $manager = new \local_coursefiles\form\native_filemanager(
            'files_filemanager',
            'Files',
            ['id' => 'id_files'],
            ['subdirs' => true, 'maxfiles' => -1, 'accepted_types' => '*']
        );
        $manager->setValue(file_get_unused_draft_itemid());
        $manager->toHtml();
        $token = \local_coursefiles\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$manager->getValue(),
            sesskey()
        );
        $this->assertNotEmpty($token);
        $this->assertSame('folder', \local_coursefiles\local\selection\draft_scope::describe($token, sesskey())['module']);
    }

    /**
     * Review trees retain Moodle escaping for arbitrary source file and folder names.
     */
    public function test_review_tree_sorts_and_escapes_names(): void {
        global $OUTPUT;
        $this->resetAfterTest();
        $this->setAdminUser();
        $nodes = \local_coursefiles\output\review_tree::data([
            ['filepath' => '/notes/', 'filename' => '<script>alert(1)</script>.txt', 'decision' => 'copy',
                'snapshot' => ['author' => 'Original teacher', 'license' => 'cc']],
            ['filepath' => '/', 'filename' => 'a.txt', 'decision' => 'skip_content',
                'snapshot' => ['author' => 'Original teacher', 'license' => 'cc']],
        ]);
        $this->assertSame('notes', $nodes[0]['name']);
        $this->assertTrue($nodes[0]['folder']);
        $html = $OUTPUT->render_from_template(
            'local_coursefiles/local/selection/review',
            ['nodes' => $nodes, 'backurl' => '#', 'count' => 2, 'size' => '1 KB']
        );
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringContainsString('Original teacher', $html);
    }

    /**
     * Create isolated published handouts and a destination course.
     *
     * @return array Fixture IDs.
     */
    private function fixture(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        return $this->getDataGenerator()->get_plugin_generator('local_coursefiles')
            ->create_folder_fixture(['name' => 'Public area acceptance']);
    }

    /**
     * Register an actual actor-owned native Folder draft for fragment authorization.
     *
     * @param array $fixture Fixture IDs.
     * @return array Scope token and owning module context.
     */
    private function scope(array $fixture): array {
        global $USER;
        $context = \context_module::instance($fixture['targetcmid']);
        $draftid = file_get_unused_draft_itemid();
        get_file_storage()->create_directory(\context_user::instance($USER->id)->id, 'user', 'draft', $draftid, '/');
        $token = \local_coursefiles\local\selection\draft_scope::open_contract([
            'field' => 'files_filemanager', 'draftid' => $draftid,
            'limits' => ['maxfiles' => -1, 'maxbytes' => 0, 'areamaxbytes' => 0,
                'subdirs' => true, 'accepted_types' => ['*']],
        ], $context);
        return [$token, $context];
    }

    /**
     * Create a teaching file through the native File API.
     *
     * @param \cm_info $cm Owning module.
     * @param string $area File area.
     * @param string $name Filename.
     * @param int $itemid Item ID.
     * @return \stored_file Source file.
     */
    private function file(\cm_info $cm, string $area, string $name, int $itemid = 0): \stored_file {
        return get_file_storage()->create_file_from_string(['contextid' => $cm->context->id,
            'component' => 'mod_' . $cm->modname, 'filearea' => $area, 'itemid' => $itemid,
            'filepath' => '/', 'filename' => $name, 'author' => 'Original teacher', 'license' => 'cc'], $name);
    }
}
