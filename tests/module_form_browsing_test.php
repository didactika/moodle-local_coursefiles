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
 * Browsing regression contracts.
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\catalog\material_source
 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\selection\selection_options
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\indexer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\catalog\material_source::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\profile\profiles::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_filebank\local\selection\selection_options::class)]
final class module_form_browsing_test extends module_form_fixture {
    /**
     * Browser navigation retains mixed choices and excludes destination activity content.
     */
    public function test_browser_shows_folders_then_files_and_retains_selection(): void {
        [$course, $cm, $profileid, $source, $resource] = $this->fixture();
        $form = $this->request($course, $cm);
        $form->render();
        $control = \local_filebank\fixture\native_form::control($form, 'files');
        $token = \local_filebank\local\selection\draft_scope::find(
            \context_module::instance($cm->id),
            (int)$control->getValue(),
            sesskey()
        );
        $scope = \local_filebank\local\selection\draft_scope::describe($token, sesskey());
        $state = ['profileid' => $profileid, 'selected' => ['file:' . $resource->get_pathnamehash()],
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => '', 'mimetype' => ''],
            'after' => '', 'folder' => ''];
        $page = \local_filebank\local\selection\browser_listing::page($scope, $state);
        $this->assertContains('Source materials', array_column($page['cards'], 'title'));
        $this->assertNotContains('existing.txt', array_column($page['cards'], 'title'));
        $state['folder'] = 'folder:' . $source->cmid . ':' . base64_encode('/');
        $page = \local_filebank\local\selection\browser_listing::page($scope, $state);
        $this->assertContains('first.txt', array_column($page['cards'], 'title'));
        $this->assertContains('notes', array_column($page['cards'], 'title'));
        $this->assertSame([['value' => 'file:' . $resource->get_pathnamehash()]], $page['retained']);
    }
}
