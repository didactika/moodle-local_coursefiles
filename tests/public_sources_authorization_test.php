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
 * FileBank native material import integration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\catalog\catalog;
use local_filebank\local\catalog\indexer;
use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/public_sources_fixture.php');

/**
 * Authorization regression contracts.
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 */
final class public_sources_authorization_test extends public_sources_fixture {
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
        $url = $this->getDataGenerator()->create_module(
            'url',
            ['course' => $course->id,
                'externalurl' => 'https://example.org', 'intro' => 'Existing description', 'introformat' => FORMAT_HTML]
        );
        $cm = get_coursemodule_from_id('url', $url->cmid);
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/url/mod_form.php');
        $PAGE->set_cm($cm, $course);
        $PAGE->set_url('/course/modedit.php', ['update' => $cm->id]);
        \local_filebank\form\native_filemanager::register_native();
        [$cm, , , $data, $section] = get_moduleinfo_data($cm, $course);
        $form = new \mod_url_mod_form($data, $section->section, $cm, $course);
        $form->set_data($data);
        $html = $form->render();
        $this->assertStringNotContainsString('data-filebank-open', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $field = (new \DOMXPath($dom))->query('//input[@name="introeditor[itemid]"]')->item(0);
        $this->assertNotNull($field);
        $scope = \local_filebank\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$field->getAttribute('value'),
            sesskey()
        );
        $this->assertNotEmpty($scope);
        $contract = \local_filebank\local\selection\draft_scope::describe($scope, sesskey());
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
        $editor = new \local_filebank\form\native_editor(
            'introeditor',
            'Description',
            ['id' => 'id_introeditor'],
            ['context' => $context, 'maxfiles' => -1, 'subdirs' => true]
        );
        $draftid = file_get_unused_draft_itemid();
        $editor->setValue(['text' => 'Existing description', 'format' => FORMAT_HTML, 'itemid' => $draftid]);
        $editor->toHtml();
        $this->assertSame('', \local_filebank\local\selection\draft_scope::find($context, $draftid, sesskey()));
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
        $manager = new \local_filebank\form\native_filemanager(
            'files_filemanager',
            'Files',
            ['id' => 'id_files'],
            ['subdirs' => true, 'maxfiles' => -1, 'accepted_types' => '*']
        );
        $manager->setValue(file_get_unused_draft_itemid());
        $manager->toHtml();
        $token = \local_filebank\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$manager->getValue(),
            sesskey()
        );
        $this->assertNotEmpty($token);
        $this->assertSame('folder', \local_filebank\local\selection\draft_scope::describe($token, sesskey())['module']);
    }

    /**
     * Review trees retain Moodle escaping for arbitrary source file and folder names.
     */
    public function test_review_tree_sorts_and_escapes_names(): void {
        global $OUTPUT;
        $this->resetAfterTest();
        $this->setAdminUser();
        $nodes = \local_filebank\output\review_tree::data(
            [
                ['filepath' => '/notes/', 'filename' => '<script>alert(1)</script>.txt', 'decision' => 'copy',
                    'snapshot' => ['author' => 'Original teacher', 'license' => 'cc']],
                ['filepath' => '/', 'filename' => 'a.txt', 'decision' => 'skip_content',
                    'snapshot' => ['author' => 'Original teacher', 'license' => 'cc']],
            ]
        );
        $this->assertSame('notes', $nodes[0]['name']);
        $this->assertTrue($nodes[0]['folder']);
        $html = $OUTPUT->render_from_template(
            'local_filebank/local/selection/review',
            ['nodes' => $nodes, 'backurl' => '#', 'count' => 2, 'size' => '1 KB']
        );
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringContainsString('Original teacher', $html);
    }
}
