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
 * Published course edition fixtures for browser acceptance.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_multipicker\local\profile\profiles;
use local_multipicker\local\catalog\indexer;
use local_multipicker\local\catalog\material_source;

/**
 * Native folder form fixtures, isolated by Moodle's test environment.
 */
final class local_multipicker_generator extends component_generator_base {
    /**
     * Create exact source/destination placement for the administration workflow.
     *
     * @param array $record Fixture name.
     * @return array Fixture identifiers.
     */
    public function create_migration_fixture(array $record): array {
        global $DB, $USER;
        $fixture = $this->create_folder_fixture($record);
        $previoususer = $USER;
        $this->set_user(get_admin());
        try {
            [$profile, $revision, $rules] = profiles::load($fixture['profileid']);
            $config = $rules->get_config();
            $config['migration']['source_module']['equals'] = 'Published materials';
            $config['migration']['target_module']['equals'] = 'Target materials';
            profiles::save(json_encode($config), $profile->id);
            foreach (['CB2024', 'CB2025', 'CB2026'] as $shortname) {
                $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
                indexer::refresh($course->id);
            }
        } finally {
            $this->set_user($previoususer);
        }
        return $fixture;
    }

    /**
     * Create published editions and a teacher enrolled only in the destination.
     *
     * @param array $record Fixture name.
     * @return array Fixture identifiers.
     */
    public function create_folder_fixture(array $record): array {
        global $CFG, $DB, $USER;
        $previoususer = $USER;
        $this->set_user(get_admin());
        try {
            $example = $CFG->dirroot . '/local/multipicker/examples/institutional-profile.json';
            $config = json_decode(file_get_contents($example), true);
            $config['name'] = $record['name'];
            $config['publication']['mode'] = 'explicit-family-publication';
            $profileid = profiles::save(json_encode($config));
            set_config('author_policy', 'editable_with_provenance', 'local_multipicker');
            $uuid = \core\uuid::generate();
            $courses = [];
            foreach ([2024, 2025, 2026] as $edition) {
                $courses[$edition] = $this->datagenerator->create_course([
                    'fullname' => 'MultiPicker ' . $edition, 'shortname' => 'CB' . $edition,
                    'idnumber' => $uuid . '||CB||' . $edition,
                ]);
                $folder = $this->datagenerator->create_module('folder', [
                    'course' => $courses[$edition]->id, 'name' => $edition === 2026 ? 'Target materials' : 'Published materials',
                ]);
                $this->create_file(
                    $folder->cmid,
                    'mod_folder',
                    $edition === 2026 ? 'existing.txt' : 'lesson.txt',
                    $edition === 2026 ? 'Existing destination content' : 'Shared lesson content'
                );
                if ($edition !== 2026) {
                    get_file_storage()->create_file_from_string([
                        'contextid' => context_module::instance($folder->cmid)->id, 'component' => 'mod_folder',
                        'filearea' => 'content', 'itemid' => 0, 'filepath' => '/notes/',
                        'filename' => 'notes' . $edition . '.txt', 'author' => 'Original teacher', 'license' => 'cc',
                    ], 'Notes from ' . $edition);
                }
                if ($edition === 2026) {
                    $targetcmid = $folder->cmid;
                } else {
                    material_source::publish($profileid, $folder->cmid, false);
                }
            }
            $resource = $this->datagenerator->create_module('resource', [
                'course' => $courses[2025]->id, 'name' => 'Published resource',
            ]);
            get_file_storage()->delete_area_files(
                context_module::instance($resource->cmid)->id,
                'mod_resource',
                'content',
                0
            );
            $this->create_file($resource->cmid, 'mod_resource', 'resource.txt', 'Resource content');
            $sourcefile = get_file_storage()->get_file(
                context_module::instance($resource->cmid)->id,
                'mod_resource',
                'content',
                0,
                '/',
                'resource.txt'
            );
            $sourcefile->set_sortorder(1);
            material_source::publish($profileid, $resource->cmid, false);
            $targetresource = $this->datagenerator->create_module('resource', [
                'course' => $courses[2026]->id, 'name' => 'Target resource', 'display' => RESOURCELIB_DISPLAY_EMBED,
            ]);
            get_file_storage()->delete_area_files(
                context_module::instance($targetresource->cmid)->id,
                'mod_resource',
                'content',
                0
            );
            $this->create_file($targetresource->cmid, 'mod_resource', 'existing.txt', 'Existing resource content');
            $mainfile = get_file_storage()->get_file(
                context_module::instance($targetresource->cmid)->id,
                'mod_resource',
                'content',
                0,
                '/',
                'existing.txt'
            );
            $mainfile->set_sortorder(1);
            $assignment = $this->datagenerator->create_module('assign', [
                'course' => $courses[2025]->id, 'name' => 'Public assignment handouts', 'alwaysshowdescription' => 1,
            ]);
            foreach (['introattachment' => 'handout.txt', 'submission_files' => 'student-private.txt'] as $area => $filename) {
                get_file_storage()->create_file_from_string([
                    'contextid' => context_module::instance($assignment->cmid)->id,
                    'component' => $area === 'introattachment' ? 'mod_assign' : 'assignsubmission_file',
                    'filearea' => $area, 'itemid' => $area === 'introattachment' ? 0 : 1,
                    'filepath' => '/', 'filename' => $filename, 'author' => 'Original teacher', 'license' => 'cc',
                ], $filename);
            }
            material_source::publish($profileid, $assignment->cmid, false);
            $this->datagenerator->create_module('url', ['course' => $courses[2026]->id,
                'name' => 'Target URL', 'externalurl' => 'https://example.org',
                'intro' => '<p>Existing description</p>', 'introformat' => FORMAT_HTML]);
            $hidden = $this->datagenerator->create_module('folder', [
                'course' => $courses[2025]->id, 'name' => 'Hidden materials', 'visible' => 0,
            ]);
            $this->create_file($hidden->cmid, 'mod_folder', 'hidden.txt', 'Hidden content');
            foreach ($courses as $course) {
                indexer::refresh($course->id);
            }
            $teacher = $this->datagenerator->create_user(['username' => 'cbteacher', 'password' => 'cbteacher']);
            $this->datagenerator->enrol_user($teacher->id, $courses[2026]->id, 'editingteacher');
            if (is_file($CFG->dirroot . '/repository/multipicker/lib.php')) {
                require_once($CFG->dirroot . '/repository/multipicker/lib.php');
                (new \repository_type('multipicker'))->create(false);
            }
            return ['targetcmid' => $targetcmid, 'targetcourse' => $courses[2026]->id, 'profileid' => $profileid,
                'teacherid' => $teacher->id, 'wwwroot' => $CFG->wwwroot];
        } finally {
            $this->set_user($previoususer);
        }
    }

    /**
     * Store material through the native File API.
     *
     * @param int $cmid Source module.
     * @param string $component Native owner.
     * @param string $filename Filename.
     * @param string $content Content.
     */
    private function create_file(int $cmid, string $component, string $filename, string $content): void {
        get_file_storage()->create_file_from_string([
            'contextid' => context_module::instance($cmid)->id, 'component' => $component,
            'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => $filename,
            'author' => 'Original teacher', 'license' => 'cc',
        ], $content);
    }
}
