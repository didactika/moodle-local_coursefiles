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

/**
 * Shared native fixture setup for focused acceptance contracts.
 */
abstract class public_sources_fixture extends \advanced_testcase {
    /**
     * Create isolated published handouts and a destination course.
     *
     * @return array Fixture IDs.
     */
    protected function fixture(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        return $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_folder_fixture(['name' => 'Public area acceptance']);
    }

    /**
     * Register an actual actor-owned native Folder draft for fragment authorization.
     *
     * @param array $fixture Fixture IDs.
     * @return array Scope token and owning module context.
     */
    protected function scope(array $fixture): array {
        global $USER;
        $context = \context_module::instance($fixture['targetcmid']);
        $draftid = file_get_unused_draft_itemid();
        get_file_storage()->create_directory(\context_user::instance($USER->id)->id, 'user', 'draft', $draftid, '/');
        $token = \local_filebank\local\selection\draft_scope::open_contract(
            [
                'field' => 'files_filemanager', 'draftid' => $draftid,
                'limits' => ['maxfiles' => -1, 'maxbytes' => 0, 'areamaxbytes' => 0,
                    'subdirs' => true, 'accepted_types' => ['*']],
            ],
            $context
        );
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
    protected function file(\cm_info $cm, string $area, string $name, int $itemid = 0): \stored_file {
        return get_file_storage()->create_file_from_string(
            ['contextid' => $cm->context->id,
                'component' => 'mod_' . $cm->modname, 'filearea' => $area, 'itemid' => $itemid,
                'filepath' => '/', 'filename' => $name, 'author' => 'Original teacher', 'license' => 'cc'],
            $name
        );
    }
}
