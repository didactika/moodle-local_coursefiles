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
 * Native reviewed batch tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\selection\draft_import;
use local_filebank\local\selection\material_selection;
use local_filebank\local\profile\profiles;

/**
 * Shared native fixture setup for focused acceptance contracts.
 */
abstract class draft_import_fixture extends \advanced_testcase {
    /**
     * @var array Authoritative fixture form options.
     */
    protected const LIMITS = ['maxfiles' => 20, 'maxbytes' => 1024, 'areamaxbytes' => 10240,
        'subdirs' => true, 'accepted_types' => ['*']];

    /**
     * Create real source folders and an existing native destination draft.
     *
     * @return array Profile, target, source module, files, draft and destination module.
     */
    protected function fixture(): array {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/filelib.php');
        $config = $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
        $profileid = profiles::save(json_encode($config));
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $source = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2025']);
        $target = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2026']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id]);
        $fs = get_file_storage();
        $files = [];
        $declarations = [['/', 'first.txt', 'First author', 'cc'],
            ['/notes/', 'second.txt', 'Second author', 'allrightsreserved']];
        foreach ($declarations as $item) {
            $files[] = $fs->create_file_from_string(
                ['contextid' => \context_module::instance($folder->cmid)->id,
                    'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                    'filepath' => $item[0], 'filename' => $item[1], 'author' => $item[2], 'license' => $item[3]],
                $item[1]
            );
        }
        $destination = $this->getDataGenerator()->create_module('folder', ['course' => $target->id]);
        $context = \context_module::instance($destination->cmid);
        $fs->create_file_from_string(
            ['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
                'itemid' => 0, 'filepath' => '/', 'filename' => 'existing.txt', 'author' => 'Existing author'],
            'existing'
        );
        $draftid = 0;
        file_prepare_draft_area($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        return [$profileid, $target, $folder, $files, $draftid, $destination];
    }
}
