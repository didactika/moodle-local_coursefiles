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

/**
 * Shared native fixture setup for focused acceptance contracts.
 */
abstract class module_form_fixture extends \advanced_testcase {
    /**
     * Create current source files, publication and a destination-only teacher.
     *
     * @param string $modulename Destination module type.
     * @return array Destination course/module, profile, source folder and resource file.
     */
    protected function fixture(string $modulename = 'folder'): array {
        global $CFG, $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once(dirname(__DIR__) . '/fixtures/native_form.php');
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/folder/mod_form.php');
        require_once($CFG->dirroot . '/mod/resource/mod_form.php');
        $config = $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
        $config['publication']['mode'] = 'explicit-family-publication';
        $profileid = profiles::save(json_encode($config));
        set_config('author_policy', 'editable_with_provenance', 'local_filebank');
        $uuid = \core\uuid::generate();
        $source = $this->getDataGenerator()->create_course(['shortname' => 'SOURCE2025', 'idnumber' => $uuid . '||CB||2025']);
        $target = $this->getDataGenerator()->create_course(['shortname' => 'TARGET2026', 'idnumber' => $uuid . '||CB||2026']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id, 'name' => 'Source materials']);
        $fs = get_file_storage();
        foreach ([['/', 'first.txt'], ['/notes/', 'second.txt']] as $file) {
            $fs->create_file_from_string(
                ['contextid' => \context_module::instance($folder->cmid)->id,
                    'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0,
                    'filepath' => $file[0], 'filename' => $file[1], 'author' => 'Author ' . $file[1], 'license' => 'cc'],
                $file[1]
            );
        }
        $resourcemodule = $this->getDataGenerator()->create_module(
            'resource',
            [
                'course' => $source->id, 'name' => 'Extra resource',
            ]
        );
        $resource = $fs->create_file_from_string(
            ['contextid' => \context_module::instance($resourcemodule->cmid)->id,
                'component' => 'mod_resource', 'filearea' => 'content', 'itemid' => 0,
                'filepath' => '/', 'filename' => 'extra.txt', 'author' => 'Resource author', 'license' => 'cc'],
            'extra'
        );
        material_source::publish($profileid, $folder->cmid, false);
        material_source::publish($profileid, $resourcemodule->cmid, false);
        $destination = $this->getDataGenerator()->create_module(
            $modulename,
            [
                'course' => $target->id, 'name' => 'Target materials',
            ]
        );
        $cm = get_coursemodule_from_id($modulename, $destination->cmid, $target->id, false, MUST_EXIST);
        // The resource test generator creates a sample file; establish an explicit one-file fixture instead.
        $fs->delete_area_files(\context_module::instance($cm->id)->id, 'mod_' . $modulename, 'content', 0);
        $fs->create_file_from_string(
            ['contextid' => \context_module::instance($cm->id)->id,
                'component' => 'mod_' . $modulename, 'filearea' => 'content', 'itemid' => 0,
                'filepath' => '/', 'filename' => 'existing.txt', 'author' => 'Existing author', 'license' => 'cc',
                'sortorder' => $modulename === 'resource' ? 1 : 0],
            'existing'
        );
        indexer::refresh($source->id);
        indexer::refresh($target->id);
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $target->id, 'editingteacher');
        $this->setUser($teacher);
        $PAGE->set_course($target);
        $PAGE->set_cm($cm, $target);
        \local_filebank\form\native_filemanager::register_native();
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
    protected function request(\stdClass $course, \stdClass $cm, array $submission = []): \moodleform_mod {
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
    protected function hidden(string $html): array {
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
