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
 *  regression contracts.
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\catalog::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\material_source::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
final class public_sources_test extends public_sources_fixture {
    /**
     * Overlapping edition folders select each set of bytes once and synchronize their checks.
     */
    public function test_overlapping_folders_share_canonical_content_selection(): void {
        $fixture = $this->fixture();
        [$token] = $this->scope($fixture);
        $controller = \local_filebank\local\selection\browser_controller::class;
        $result = $controller::data(
            $token,
            $controller::input(
                [
                    'profileid' => $fixture['profileid'], 'module' => 'folder', 'selectall' => true,
                ]
            )
        );
        $this->assertSame('', $result['message']);
        $this->assertSame(3, $result['data']['count']);
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $records = \local_filebank\local\selection\selected_content::records($scope['view']['selected']);
        $this->assertCount(3, array_unique(array_column($records, 'contenthash')));
        foreach (json_decode($result['data']['folderstates'], true) as $state) {
            $this->assertTrue($state['checked']);
            $this->assertFalse($state['partial']);
        }
        $scope['view']['selected'] = array_column($result['data']['cards'], 'value');
        \local_filebank\local\selection\draft_state::remember_view($token, $scope['view'], sesskey());
        $this->assertSame(3, $controller::data($token)['data']['count']);
    }

    /**
     * AJAX filters cannot render another native control's context or perform review actions.
     */
    public function test_fragment_rejects_an_unrelated_context(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/filebank/lib.php');
        [$token, $context] = $this->scope($this->fixture());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('formscopeunavailable', 'local_filebank'));
        \local_filebank_output_fragment_browser(
            ['scope' => $token,
                'context' => $context->get_course_context(), 'state' => '{}']
        );
    }

    /**
     * Off-page selections survive a live search; submitted review actions cannot authorize transfer.
     */
    public function test_fragment_keeps_selection_without_approving_transfer(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/filebank/lib.php');
        $fixture = $this->fixture();
        [$token, $context] = $this->scope($fixture);
        $state = \local_filebank\local\selection\browser_controller::data($token)['data'];
        $selected = $state['cards'][0]['value'];
        $html = \local_filebank_output_fragment_browser(
            ['scope' => $token, 'context' => $context,
                'state' => json_encode(
                    ['profileid' => $fixture['profileid'], 'items' => [$selected],
                        'q' => 'no-such-teaching-material', 'review' => true]
                )]
        );
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $this->assertSame([$selected], $scope['view']['selected']);
        $this->assertArrayNotHasKey('review', $scope);
        $this->assertStringContainsString('data-filebank-panel', $html);
        $this->assertStringNotContainsString('data-filebank-confirm', $html);
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
        $result = \local_filebank\local\selection\browser_controller::data(
            $token,
            \local_filebank\local\selection\browser_controller::input(
                [
                    'profileid' => $fixture['profileid'], 'q' => 'batch-match-', 'selectall' => true,
                ]
            )
        );
        $this->assertSame('', $result['message']);
        $this->assertSame(61, $result['data']['count']);
        $this->assertCount(50, $result['data']['cards']);
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $this->assertCount(61, $scope['view']['selected']);
    }

    /**
     * Toggling descendants and refreshing filters keep the current source directory.
     */
    public function test_fragment_keeps_folder_and_recursive_selection(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/filebank/lib.php');
        $fixture = $this->fixture();
        [$token, $context] = $this->scope($fixture);
        $source = $DB->get_record('course', ['shortname' => 'CB2025']);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        $value = 'folder:' . $folder->id . ':' . base64_encode('/');
        $input = ['profileid' => $fixture['profileid'], 'items' => [], 'toggleitem' => $value, 'openfolder' => $value];
        \local_filebank\local\selection\browser_controller::data(
            $token,
            \local_filebank\local\selection\browser_controller::input($input)
        );
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $this->assertCount(2, $scope['view']['selected']);
        $file = get_file_storage()->get_file($folder->context->id, 'mod_folder', 'content', 0, '/', 'lesson.txt');
        $html = \local_filebank_output_fragment_browser(
            ['scope' => $token, 'context' => $context,
                'state' => json_encode(
                    ['profileid' => $fixture['profileid'], 'items' => $scope['view']['selected'],
                        'toggleitem' => 'file:' . $file->get_pathnamehash()]
                )]
        );
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $this->assertSame($value, $scope['view']['folder']);
        $this->assertCount(1, $scope['view']['selected']);
        $this->assertStringContainsString('filebank-back', $html);
    }

    /**
     * The embedded title uses the repository instance name rather than the component label.
     */
    public function test_browser_uses_repository_instance_name(): void {
        $fixture = $this->fixture();
        [$token] = $this->scope($fixture);
        \local_filebank\local\selection\draft_state::select_repository($token, 'Course library', sesskey());
        $data = \local_filebank\local\selection\browser_controller::data($token)['data'];
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
        $DB->update_record(
            'assign',
            (object)['id' => $assignment->instance, 'alwaysshowdescription' => 0,
                'allowsubmissionsfromdate' => time() + DAYSECS]
        );
        $this->file($assignment, 'activityattachment', 'future-instructions.txt');
        indexer::refresh($source->id);
        $result = catalog::search($fixture['targetcourse'], $fixture['profileid'], ['module' => 'assign']);
        $this->assertCount($CFG->version >= 2026100500 ? 1 : 0, $result['assets']);
        foreach ($result['assets'] as $asset) {
            $this->assertSame('handout.txt', $asset['occurrences'][0]['filename']);
        }
    }
}
