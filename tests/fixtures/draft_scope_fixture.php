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

/**
 * Shared native fixture setup for focused acceptance contracts.
 */
abstract class draft_scope_fixture extends \advanced_testcase {
    /**
     * Build an actual owning form for an editor with an explicit source publication grant.
     *
     * @return array Form, profile, source module, destination module, context.
     */
    protected function fixture(): array {

        global $CFG, $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/formslib.php');
        require_once(dirname(__DIR__) . '/fixtures/scoped_form.php');
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $config = $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
        $config['publication']['mode'] = 'explicit-family-publication';
        $profileid = profiles::save(json_encode($config));
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $sourcecourse = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2025']);
        $targetcourse = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MATH||2026', 'maxbytes' => 4096]);
        $source = $this->getDataGenerator()->create_module('folder', ['course' => $sourcecourse->id]);
        $target = $this->getDataGenerator()->create_module('folder', ['course' => $targetcourse->id]);
        $fs = get_file_storage();
        foreach ([['/', 'first.txt', 'First author'], ['/notes/', 'second.txt', 'Second author']] as $file) {
            $fs->create_file_from_string(
                ['contextid' => \context_module::instance($source->cmid)->id,
                    'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                    'filepath' => $file[0], 'filename' => $file[1], 'author' => $file[2], 'license' => 'cc'],
                $file[1]
            );
        }
        $context = \context_module::instance($target->cmid);
        $fs->create_file_from_string(
            ['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
                'itemid' => 0, 'filepath' => '/', 'filename' => 'existing.txt'],
            'existing'
        );
        material_source::publish($profileid, $source->cmid, false);
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $targetcourse->id, 'editingteacher');
        $this->setUser($teacher);
        $PAGE->set_course($targetcourse);
        $PAGE->set_context($context);
        $PAGE->set_url('/course/modedit.php', ['update' => $target->cmid]);
        $draftid = 0;
        file_prepare_draft_area($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        $form = new scoped_form(
            null,
            ['maxfiles' => 4, 'maxbytes' => 1024, 'areamaxbytes' => 2048,
                'subdirs' => true, 'accepted_types' => '.TXT, .PDF']
        );
        $form->set_data((object)['title' => 'Unsaved title', 'files' => $draftid]);
        return [$form, $profileid, $source, $target, $context];
    }
}
