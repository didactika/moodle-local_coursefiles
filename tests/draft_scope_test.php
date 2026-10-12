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
 * Owning native form scope integration checks.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\fixture\scoped_form;
use local_filebank\form\scoped_filemanager;
use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\selection\draft_scope;
use local_filebank\local\selection\draft_state;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/draft_scope_fixture.php');

/**
 *  regression contracts.
 * @covers \local_filebank\form\scoped_filemanager
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\selection\draft_scope
 * @covers \local_filebank\local\selection\destination_content
 * @covers \local_filebank\local\selection\browser_controller
 * @covers \local_filebank\local\selection\browser_listing
 * @covers ::local_filebank_output_fragment_browser_tree
 */
final class draft_scope_test extends draft_scope_fixture {
    /**
     * Clearing restrictions must retain the actor's chosen content, profile and owning destination.
     */
    public function test_clear_filters_preserves_native_selection_and_profile(): void {
        [$form, $profileid, $source, $target, $context] = $this->fixture();
        \local_filebank\local\catalog\indexer::refresh($source->course);
        \local_filebank\local\catalog\indexer::refresh($target->course);
        $token = $form->control()->open_scope($context);
        $controller = \local_filebank\local\selection\browser_controller::class;
        $selected = $controller::data($token, $controller::input(['profileid' => $profileid, 'selectall' => true]));
        $items = array_column($selected['data']['selecteditems'], 'value');
        $filtered = $controller::data(
            $token,
            $controller::input(
                ['profileid' => $profileid, 'items' => $items,
                    'q' => 'missing', 'module' => 'folder', 'shortname' => 'missing', 'captures' => ['edition' => '1900'],
                    'refresh' => true, 'layout' => 'list']
            )
        );
        $this->assertSame(4, $filtered['data']['filtercount']);
        $cleared = $controller::data(
            $token,
            $controller::input(
                ['profileid' => $profileid, 'items' => $items,
                    'q' => 'missing', 'module' => 'folder', 'shortname' => 'missing', 'captures' => ['edition' => '1900'],
                    'clearfilters' => true, 'layout' => 'list']
            )
        );
        $this->assertSame('', $cleared['message']);
        $this->assertSame(0, $cleared['data']['filtercount']);
        $this->assertSame($selected['data']['selecteditems'], $cleared['data']['selecteditems']);
        $this->assertSame(2, $cleared['data']['count']);
        $this->assertSame($profileid, $cleared['data']['profileid']);
        $this->assertSame('list', $cleared['data']['layout']);
        $this->assertNotEmpty($cleared['data']['cards']);
    }

    /**
     * Existing bytes are excluded before pagination, even when the destination file was renamed.
     */
    public function test_existing_draft_content_is_hidden_and_not_selected_with_its_folder(): void {
        global $USER;
        [$form, $profileid, $source, $target, $context] = $this->fixture();
        \local_filebank\local\catalog\indexer::refresh($source->course);
        \local_filebank\local\catalog\indexer::refresh($target->course);
        $draftid = (int)$form->control()->getValue();
        $fs = get_file_storage();
        $original = $fs->get_file(
            \context_module::instance($source->cmid)->id,
            'mod_folder',
            'content',
            0,
            '/',
            'first.txt'
        );
        $existing = $fs->create_file_from_storedfile(
            ['contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
                'filepath' => '/renamed/', 'filename' => 'already-used.txt', 'author' => 'Changed native author'],
            $original
        );
        $token = $form->control()->open_scope($context);
        $data = \local_filebank\local\selection\browser_controller::data($token);
        $root = array_values(array_filter($data['data']['cards'], static fn($card) => $card['isfolder']))[0];
        $this->assertSame('0 / 1 selected', $root['selectionlabel']);
        $result = \local_filebank\local\selection\browser_controller::data(
            $token,
            \local_filebank\local\selection\browser_controller::input(['profileid' => $profileid, 'selectall' => true])
        );
        $this->assertSame(1, $result['data']['count']);
        $this->assertNotContains(['value' => 'file:' . $original->get_pathnamehash()], $result['data']['selecteditems']);
        $assets = \local_filebank\local\catalog\catalog::search($target->course, $profileid, [], '', 1, $draftid);
        $this->assertCount(1, $assets['assets']);
        $this->assertSame('second.txt', $assets['assets'][0]['occurrences'][0]['filename']);
        $this->assertSame('', $assets['next']);
        $existing->delete();
        $assets = \local_filebank\local\catalog\catalog::search($target->course, $profileid, [], '', 50, $draftid);
        $this->assertCount(2, $assets['assets']);
    }

    /**
     * Another actor's draft with the same item ID cannot hide available material.
     */
    public function test_destination_exclusion_is_actor_scoped(): void {
        [$form, $profileid, $source, $target] = $this->fixture();
        \local_filebank\local\catalog\indexer::refresh($source->course);
        \local_filebank\local\catalog\indexer::refresh($target->course);
        $actor = $this->getDataGenerator()->create_user();
        $file = get_file_storage()->get_file(
            \context_module::instance($source->cmid)->id,
            'mod_folder',
            'content',
            0,
            '/',
            'first.txt'
        );
        get_file_storage()->create_file_from_storedfile(
            ['contextid' => \context_user::instance($actor->id)->id,
                'component' => 'user', 'filearea' => 'draft', 'itemid' => (int)$form->control()->getValue()],
            $file
        );
        $assets = \local_filebank\local\catalog\catalog::search(
            $target->course,
            $profileid,
            [],
            '',
            50,
            (int)$form->control()->getValue()
        );
        $this->assertCount(2, $assets['assets']);
    }

    /**
     * Lazy children preserve the owning cursor and reject folders not issued by the authorized browser.
     */
    public function test_tree_branches_load_files_without_changing_browser_navigation(): void {
        global $CFG;
        [$form, , $source, , $context] = $this->fixture();
        require_once($CFG->dirroot . '/local/filebank/lib.php');
        \local_filebank\local\catalog\indexer::refresh($source->course);
        \local_filebank\local\catalog\indexer::refresh($context->get_course_context()->instanceid);
        $token = $form->control()->open_scope($context);
        \local_filebank\local\selection\browser_controller::data($token);
        $args = ['context' => $context, 'scope' => $token, 'folder' => 'folder:' . $source->cmid . ':' . base64_encode('/')];
        $html = \local_filebank_output_fragment_browser_tree($args);
        $this->assertStringContainsString('first.txt', $html);
        $this->assertStringNotContainsString('second.txt', $html);
        $args['folder'] = 'folder:' . $source->cmid . ':' . base64_encode('/notes/');
        $html = \local_filebank_output_fragment_browser_tree($args);
        $this->assertStringContainsString('second.txt', $html);
        $view = draft_scope::describe($token, sesskey())['view'];
        $this->assertSame('', $view['folder']);
        $this->assertSame('', $view['after']);
        $args['folder'] = 'folder:' . $context->instanceid . ':' . base64_encode('/');
        try {
            \local_filebank_output_fragment_browser_tree($args);
            $this->fail('A folder outside the issued source roots was accepted.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('formscopeunavailable', $exception->errorcode);
        }
    }

    /**
     * A folder exceeding the actual field capacity cannot change the stored selection.
     */
    public function test_recursive_selection_refuses_native_capacity_before_review(): void {
        global $USER;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $draftid = (int)$form->control()->getValue();
        $fs = get_file_storage();
        for ($i = 0; $i < 2; $i++) {
            $fs->create_file_from_string(
                ['contextid' => \context_user::instance($USER->id)->id,
                    'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid, 'filepath' => '/',
                    'filename' => 'existing-' . $i . '.txt'],
                'Existing draft ' . $i
            );
        }
        $fs->create_file_from_string(
            ['contextid' => \context_module::instance($source->cmid)->id, 'component' => 'mod_folder',
                'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'additional.txt',
                'author' => 'Original teacher', 'license' => 'cc'],
            'Additional source'
        );
        \local_filebank\local\catalog\indexer::refresh($source->course);
        $token = $form->control()->open_scope($context);
        $state = ['profileid' => $profileid, 'selected' => [], 'roots' => [], 'renames' => [], 'layout' => 'grid',
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => 'folder', 'mimetype' => ''],
            'folder' => '', 'after' => '', 'history' => []];
        draft_state::remember_view($token, $state, sesskey());
        $data = \local_filebank\local\selection\browser_controller::data(
            $token,
            \local_filebank\local\selection\browser_controller::input(
                [
                    'profileid' => $profileid, 'toggleitem' => 'folder:' . $source->cmid . ':' . base64_encode('/'),
                    'module' => 'folder',
                ]
            )
        );
        $this->assertStringContainsString(get_string('selectionfull', 'local_filebank'), $data['message']);
        $this->assertSame(0, $data['data']['count']);
        $this->assertSame([], draft_scope::describe($token, sesskey())['view']['selected']);
    }

    /**
     * Prefixing source directories cannot exceed the native filepath column length.
     */
    public function test_combined_destination_path_cannot_exceed_native_storage(): void {
        global $USER;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $path = '/' . str_repeat('a', 249) . '/';
        get_file_storage()->create_directory(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$form->control()->getValue(),
            $path
        );
        $token = $form->control()->open_scope($context);
        draft_state::select_destination($token, $path, sesskey());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidselection', 'local_filebank'));
        draft_state::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
    }

    /**
     * Destination choices include only this draft and obey the real subdirectory setting.
     */
    public function test_destination_choices_and_native_subdirectory_limit(): void {
        global $USER;
        [$form, , , , $context] = $this->fixture();
        $control = $form->control();
        $fs = get_file_storage();
        $usercontext = \context_user::instance($USER->id)->id;
        $fs->create_directory($usercontext, 'user', 'draft', (int)$control->getValue(), '/chosen/');
        $otherdraft = file_get_unused_draft_itemid();
        $fs->create_directory($usercontext, 'user', 'draft', $otherdraft, '/other/');
        $token = $control->open_scope($context);
        $this->assertSame(['/' => '/', '/chosen/' => '/chosen/'], draft_state::destinations($token, sesskey()));
        $control->setSubdirs(false);
        $token = $control->open_scope($context);
        $this->assertSame(['/' => '/'], draft_state::destinations($token, sesskey()));
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('batchlimit', 'local_filebank'));
        draft_state::select_destination($token, '/chosen/', sesskey());
    }

    /**
     * A directory from another draft cannot be bound by forging a submitted path.
     */
    public function test_destination_must_exist_in_bound_draft(): void {
        global $USER;
        [$form, , , , $context] = $this->fixture();
        get_file_storage()->create_directory(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            file_get_unused_draft_itemid(),
            '/foreign/'
        );
        $token = $form->control()->open_scope($context);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_filebank'));
        draft_state::select_destination($token, '/foreign/', sesskey());
    }
}
