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
 * Owning native form scope integration checks.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker;

use local_multipicker\fixture\scoped_form;
use local_multipicker\form\scoped_filemanager;
use local_multipicker\local\catalog\material_source;
use local_multipicker\local\profile\profiles;
use local_multipicker\local\selection\draft_scope;

/**
 * Tests actual owning form options, session authority and native draft rendering.
 * @covers \local_multipicker\form\scoped_filemanager
 * @covers \local_multipicker\local\catalog\material_source
 * @covers \local_multipicker\local\profile\profiles
 * @covers \local_multipicker\local\selection\draft_scope
 */
final class draft_scope_test extends \advanced_testcase {
    /**
     * A folder exceeding the actual field capacity cannot change the stored selection.
     */
    public function test_recursive_selection_refuses_native_capacity_before_review(): void {
        global $USER;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $draftid = (int)$form->control()->getValue();
        $fs = get_file_storage();
        for ($i = 0; $i < 2; $i++) {
            $fs->create_file_from_string(['contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid, 'filepath' => '/',
                'filename' => 'existing-' . $i . '.txt'], 'Existing draft ' . $i);
        }
        $fs->create_file_from_string(['contextid' => \context_module::instance($source->cmid)->id, 'component' => 'mod_folder',
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => 'additional.txt',
            'author' => 'Original teacher', 'license' => 'cc'], 'Additional source');
        \local_multipicker\local\catalog\indexer::refresh($source->course);
        $token = $form->control()->open_scope($context);
        $state = ['profileid' => $profileid, 'selected' => [], 'roots' => [], 'renames' => [], 'layout' => 'grid',
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => 'folder', 'mimetype' => ''],
            'folder' => '', 'after' => '', 'history' => []];
        draft_scope::remember_view($token, $state, sesskey());
        $data = \local_multipicker\local\selection\browser_controller::data(
            $token,
            \local_multipicker\local\selection\browser_controller::input([
                'profileid' => $profileid, 'toggleitem' => 'folder:' . $source->cmid . ':' . base64_encode('/'),
                'module' => 'folder',
            ])
        );
        $this->assertStringContainsString(get_string('selectionfull', 'local_multipicker'), $data['message']);
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
        draft_scope::select_destination($token, $path, sesskey());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidselection', 'local_multipicker'));
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
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
        $this->assertSame(['/' => '/', '/chosen/' => '/chosen/'], draft_scope::destinations($token, sesskey()));
        $control->setSubdirs(false);
        $token = $control->open_scope($context);
        $this->assertSame(['/' => '/'], draft_scope::destinations($token, sesskey()));
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('batchlimit', 'local_multipicker'));
        draft_scope::select_destination($token, '/chosen/', sesskey());
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
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_multipicker'));
        draft_scope::select_destination($token, '/foreign/', sesskey());
    }

    /**
     * Deleting an approved destination prevents copying even when leaf snapshots are unchanged.
     */
    public function test_deleted_destination_cannot_apply(): void {
        global $USER;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $directory = get_file_storage()->create_directory(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$form->control()->getValue(),
            '/chosen/'
        );
        $token = $form->control()->open_scope($context);
        draft_scope::select_destination($token, '/chosen/', sesskey());
        $review = draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
        $directory->delete();
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_multipicker'));
        draft_scope::apply($token, $review['digest'], sesskey());
    }

    /**
     * The registered subclass uses actual native options, including MIME normalization and upload ceilings.
     */
    public function test_control_contract_matches_core(): void {
        [$form, , , , $context] = $this->fixture();
        $control = $form->control();
        $this->assertInstanceOf(\MoodleQuickForm_filemanager::class, $control);
        $contract = $control->get_contract($context);
        $native = new \form_filemanager((object)['context' => $context, 'itemid' => $contract['draftid'],
            'maxbytes' => $control->getMaxbytes(), 'maxfiles' => $control->getMaxfiles(),
            'subdirs' => $control->getSubdirs(), 'areamaxbytes' => $control->getAreamaxbytes(),
            'accepted_types' => '.TXT, .PDF']);
        $this->assertSame($native->options->maxbytes, $contract['limits']['maxbytes']);
        $this->assertSame(['.txt', '.pdf'], $contract['limits']['accepted_types']);
        $this->assertSame(4, $contract['limits']['maxfiles']);
        $this->assertSame(1024, $contract['limits']['maxbytes']);
        $this->assertSame(2048, $contract['limits']['areamaxbytes']);
        $this->assertTrue($contract['limits']['subdirs']);
    }

    /**
     * A mixed batch enters the owning draft, preserving the field's pending data and native output.
     */
    public function test_form_owned_round_trip_and_replay_rejection(): void {
        global $DB, $USER;
        [$form, $profileid, $source, $target, $context] = $this->fixture();
        $this->preventResetByRollback();
        $control = $form->control();
        $token = $control->open_scope($context);
        $items = [['cmid' => $source->cmid, 'filepath' => '/']];
        $review = draft_scope::review($token, $profileid, $items, sesskey());
        $this->assertSame(3, $review['filecount']);
        $this->assertSame(0, $DB->count_records('local_multipicker_attribution'));
        draft_scope::apply($token, $review['digest'], sesskey());
        $draftid = (int)$control->getValue();
        $files = get_file_storage()->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        );
        $this->assertCount(3, $files);
        $submission = scoped_form::mock_ajax_submit(['title' => 'Unsaved title', 'files' => $draftid, 'refresh' => 1]);
        $refreshed = new scoped_form(null, [
            'maxfiles' => 4, 'maxbytes' => 1024, 'areamaxbytes' => 2048,
            'subdirs' => true, 'accepted_types' => '.TXT, .PDF',
        ], 'post', '', null, true, $submission);
        $this->assertNull($refreshed->get_data());
        $html = $refreshed->render();
        $this->assertStringContainsString('value="Unsaved title"', $html);
        $this->assertStringContainsString('name="files"', $html);
        $this->assertStringContainsString('filemanager', $html);
        $this->assertSame($draftid, (int)$control->getValue());
        $this->assertSame($draftid, (int)$refreshed->control()->getValue());
        $this->assertSame(2, $DB->count_records('local_multipicker_attribution'));
        $this->assertCount(1, get_file_storage()->get_area_files($context->id, 'mod_folder', 'content', 0, 'id', false));
        file_save_draft_area_files($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        $this->assertCount(3, get_file_storage()->get_area_files($context->id, 'mod_folder', 'content', 0, 'id', false));
        try {
            draft_scope::apply($token, $review['digest'], sesskey());
            $this->fail('An applied approval must not be reusable.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchconflict', $exception->errorcode);
        }
        $this->assertSame(2, $DB->count_records('local_multipicker_attribution'));
    }

    /**
     * Even a valid control token cannot replace CSRF protection.
     */
    public function test_invalid_session_key_is_rejected(): void {
        [$form, $profileid, $source, , $context] = $this->fixture();
        $token = $form->control()->open_scope($context);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], 'forged');
    }

    /**
     * Scope validity does not carry over to another logged-in editor.
     */
    public function test_another_actor_cannot_use_scope(): void {
        global $SESSION;
        [$form, $profileid, $source, $target, $context] = $this->fixture();
        $token = $form->control()->open_scope($context);
        $actor = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($actor->id, $target->course, 'editingteacher');
        $scopes = $SESSION->local_multipicker_drafts;
        $this->setUser($actor);
        $SESSION->local_multipicker_drafts = $scopes;
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_multipicker'));
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
    }

    /**
     * Expired bindings stop before material resolution or draft writes.
     */
    public function test_expired_scope_is_rejected(): void {
        global $SESSION;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $token = $form->control()->open_scope($context);
        $SESSION->local_multipicker_drafts[$token]['expires'] = time() - 1;
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_multipicker'));
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
    }

    /**
     * Reopening a control invalidates its earlier token and reads changed server-side limits.
     */
    public function test_rebinding_reads_current_control_limits(): void {
        [$form, $profileid, $source, , $context] = $this->fixture();
        $control = $form->control();
        $oldtoken = $control->open_scope($context);
        $control->setMaxfiles(1);
        $newtoken = $control->open_scope($context);
        $this->assertNotSame($oldtoken, $newtoken);
        foreach ([$oldtoken => 'formscopeunavailable', $newtoken => 'batchlimit'] as $token => $errorcode) {
            try {
                draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
                $this->fail('Old authority or an exceeded limit must be rejected.');
            } catch (\moodle_exception $exception) {
                $this->assertSame($errorcode, $exception->errorcode);
            }
        }
    }

    /**
     * Smaller site limits after review invalidate the approval before copying.
     */
    public function test_changed_upload_ceiling_requires_review(): void {
        global $CFG, $DB;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $this->preventResetByRollback();
        $token = $form->control()->open_scope($context);
        $review = draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
        $CFG->maxbytes = 512;
        try {
            draft_scope::apply($token, $review['digest'], sesskey());
            $this->fail('A changed upload ceiling must invalidate approval.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('batchconflict', $exception->errorcode);
        }
        $this->assertSame(0, $DB->count_records('local_multipicker_attribution'));
    }

    /**
     * The owning form can release its binding without deleting draft files.
     */
    public function test_close_preserves_draft_and_rejects_future_use(): void {
        global $USER;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $token = $form->control()->open_scope($context);
        draft_scope::close($token, sesskey());
        $this->assertCount(1, get_file_storage()->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            (int)$form->control()->getValue(),
            'id',
            false
        ));
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_multipicker'));
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
    }

    /**
     * Frozen native fields cannot grant a writable batch scope.
     */
    public function test_frozen_control_cannot_open_scope(): void {
        [$form, , , , $context] = $this->fixture();
        $form->control()->freeze();
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_multipicker'));
        $form->control()->open_scope($context);
    }

    /**
     * New native fields can acquire an empty current-user draft before rendering.
     */
    public function test_new_field_creates_an_owned_empty_draft(): void {
        global $USER;
        [$form, , , , $context] = $this->fixture();
        $control = $form->control();
        $control->setValue(0);
        $token = $control->open_scope($context);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertGreaterThan(0, (int)$control->getValue());
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
     * Revoked activity editing authority cannot survive in a previously valid scope.
     */
    public function test_revoked_editing_authority_is_rechecked(): void {
        global $DB;
        [$form, $profileid, $source, , $context] = $this->fixture();
        $token = $form->control()->open_scope($context);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('moodle/course:manageactivities', CAP_PROHIBIT, $roleid, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        $this->expectException(\required_capability_exception::class);
        draft_scope::review($token, $profileid, [['cmid' => $source->cmid, 'filepath' => '/']], sesskey());
    }

    /**
     * A positive draft belonging to a different account cannot become an owning-form binding.
     */
    public function test_foreign_draft_cannot_open_scope(): void {
        [$form, , , , $context] = $this->fixture();
        $actor = $this->getDataGenerator()->create_user();
        $draftid = file_get_unused_draft_itemid();
        get_file_storage()->create_directory(\context_user::instance($actor->id)->id, 'user', 'draft', $draftid, '/');
        $form->control()->setValue($draftid);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invaliddraft', 'local_multipicker'));
        $form->control()->open_scope($context);
    }

    /**
     * Unclosed controls cannot grow session state without a bound.
     */
    public function test_scope_registry_is_bounded(): void {
        global $SESSION;
        [$form, , , , $context] = $this->fixture();
        $control = $form->control();
        $firsttoken = '';
        for ($index = 0; $index <= draft_scope::MAX_SCOPES; $index++) {
            $control->setName('files' . $index);
            $token = $control->open_scope($context);
            $firsttoken = $firsttoken ?: $token;
        }
        $this->assertCount(draft_scope::MAX_SCOPES, $SESSION->local_multipicker_drafts);
        $this->assertArrayNotHasKey($firsttoken, $SESSION->local_multipicker_drafts);
        $this->assertArrayHasKey($token, $SESSION->local_multipicker_drafts);
    }

    /**
     * Build an actual owning form for an editor with an explicit source publication grant.
     *
     * @return array Form, profile, source module, destination module, context.
     */
    private function fixture(): array {
        global $CFG, $PAGE, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/formslib.php');
        require_once(__DIR__ . '/fixtures/scoped_form.php');
        set_config('author_policy', 'editable_with_provenance', 'local_multipicker');
        $config = json_decode(file_get_contents($CFG->dirroot . '/local/multipicker/examples/institutional-profile.json'), true);
        $config['publication']['mode'] = 'explicit-family-publication';
        $profileid = profiles::save(json_encode($config));
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $sourcecourse = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2025']);
        $targetcourse = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2026', 'maxbytes' => 4096]);
        $source = $this->getDataGenerator()->create_module('folder', ['course' => $sourcecourse->id]);
        $target = $this->getDataGenerator()->create_module('folder', ['course' => $targetcourse->id]);
        $fs = get_file_storage();
        foreach ([['/', 'first.txt', 'First author'], ['/notes/', 'second.txt', 'Second author']] as $file) {
            $fs->create_file_from_string(['contextid' => \context_module::instance($source->cmid)->id,
                'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                'filepath' => $file[0], 'filename' => $file[1], 'author' => $file[2], 'license' => 'cc'], $file[1]);
        }
        $context = \context_module::instance($target->cmid);
        $fs->create_file_from_string(['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
            'itemid' => 0, 'filepath' => '/', 'filename' => 'existing.txt'], 'existing');
        material_source::publish($profileid, $source->cmid, false);
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $targetcourse->id, 'editingteacher');
        $this->setUser($teacher);
        $PAGE->set_course($targetcourse);
        $PAGE->set_context($context);
        $PAGE->set_url('/course/modedit.php', ['update' => $target->cmid]);
        $draftid = 0;
        file_prepare_draft_area($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        $form = new scoped_form(null, ['maxfiles' => 4, 'maxbytes' => 1024, 'areamaxbytes' => 2048,
            'subdirs' => true, 'accepted_types' => '.TXT, .PDF']);
        $form->set_data((object)['title' => 'Unsaved title', 'files' => $draftid]);
        return [$form, $profileid, $source, $target, $context];
    }
}
