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
 * Course material fixtures for browser acceptance.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_filebank\local\profile\profiles;
use local_filebank\local\catalog\indexer;
use local_filebank\local\catalog\material_source;

/**
 * Native folder form fixtures, isolated by Moodle's test environment.
 */
final class local_filebank_generator extends component_generator_base {
    /**
     * Build consistent matching, publication and placement data for isolated tests.
     *
     * @return array Test profile configuration.
     */
    public function create_profile_config(): array {
        return [
            'schema_version' => 1,
            'name' => 'Test course collection',
            'scope' => [
                'campus' => 'current',
                'category_ids' => [],
            ],
            'relationship' => [
                'ambiguity_policy' => 'block',
                'parsers' => [
                    [
                        'name' => 'uuid-code-optional-year',
                        'field' => 'idnumber',
                        'pattern' => '~^(?<family>[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}'
                            . '-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})\\|\\|(?<code>[^|]+)(?:\\|\\|(?<edition>\\d{4}))?$~u',
                        'required_captures' => [
                            'family',
                            'code',
                        ],
                        'normalizers' => [
                            'family' => [
                                'trim',
                                'lowercase',
                            ],
                            'code' => [
                                'trim',
                            ],
                        ],
                    ],
                ],
                'family_key' => '${family}',
                'display_label' => '${code}',
                'edition' => [
                    'capture' => 'edition',
                    'optional' => true,
                    'comparison' => 'string',
                ],
                'pair_dimensions' => [],
            ],
            'publication' => [
                'mode' => 'source-access-only',
                'allowed_sources' => [
                    'mod_folder/content',
                    'mod_resource/content',
                    'mod_page/content',
                    'mod_book/chapter',
                    'mod_assign/introattachment',
                    'mod_assign/activityattachment',
                    'mod_*/intro',
                ],
                'allow_hidden' => false,
                'allow_restricted' => false,
            ],
            'migration' => [
                'source_edition' => '2025',
                'target_edition' => '2026',
                'source_module' => [
                    'type' => 'folder',
                    'field' => 'name',
                    'equals' => 'materiales_de_clase.module',
                ],
                'target_module' => [
                    'type' => 'folder',
                    'field' => 'name',
                    'equals' => 'materiales_de_clase.module',
                ],
                'section_match' => [
                    'strategy' => 'ordinal',
                    'require_layout_review' => true,
                ],
                'missing_target' => 'block',
                'ambiguous_target' => 'block',
                'same_path_same_content' => 'skip',
                'same_path_different_content' => 'block',
                'same_content_different_path' => 'skip-and-report',
                'preserve_relative_paths' => true,
                'preserve_source' => true,
                'preserve_target_visibility' => true,
                'on_item_failure' => 'continue-unaffected',
            ],
        ];
    }

    /**
     * Create more than one native page for administration and whole-plan selection.
     * @param array $record Named fixture.
     * @return array Fixture identifiers.
     */
    public function create_pagination_fixture(array $record): array {
        global $DB, $USER;
        $fixture = $this->create_migration_fixture($record);
        $previoususer = $USER;
        $this->set_user(get_admin());
        try {
            $courseid = $DB->get_field('course', 'id', ['shortname' => 'CB2025'], MUST_EXIST);
            $folders = get_fast_modinfo($courseid)->get_instances_of('folder');
            $folder = reset($folders);
            for ($i = 0; $i < 60; $i++) {
                $this->create_file($folder->id, 'mod_folder', 'paged-' . $i . '.txt', 'Unique paged material ' . $i);
            }
            indexer::refresh($courseid);
            [, , $rules] = profiles::load($fixture['profileid']);
            $config = $rules->get_config();
            for ($i = 1; $i <= 26; $i++) {
                profiles::save(json_encode($config), $fixture['profileid']);
                $document = $config;
                $document['name'] = sprintf('Paged profile %02d', $i);
                $id = profiles::save(json_encode($document));
                profiles::change_state([$id], 'disable');
            }
            for ($i = 0; $i < 26; $i++) {
                \local_filebank\local\migration\migration_planner::preview($fixture['profileid'], '2025', '2026', true);
            }
            return $fixture;
        } finally {
            $this->set_user($previoususer);
        }
    }

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
            [$profile, , $rules] = profiles::load($fixture['profileid']);
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

        global $CFG, $USER;
        $previoususer = $USER;
        $this->set_user(get_admin());
        try {
            $config = $this->create_profile_config();
            $config['name'] = $record['name'];
            $config['publication']['mode'] = 'explicit-family-publication';
            $profileid = profiles::save(json_encode($config));
            set_config('author_policy', 'editable_with_provenance', 'local_filebank');
            $uuid = \core\uuid::generate();
            [$courses, $targetcmid] = $this->folder_editions($profileid, $uuid);
            $this->public_activities($courses, $profileid);
            foreach ($courses as $course) {
                indexer::refresh($course->id);
            }
            $teacher = $this->datagenerator->create_user(['username' => 'cbteacher', 'password' => 'cbteacher']);
            $this->datagenerator->enrol_user($teacher->id, $courses[2026]->id, 'editingteacher');
            if (is_file($CFG->dirroot . '/repository/filebank/lib.php')) {
                require_once($CFG->dirroot . '/repository/filebank/lib.php');
                (new \repository_type('filebank'))->create(false);
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
        get_file_storage()->create_file_from_string(
            [
                'contextid' => context_module::instance($cmid)->id, 'component' => $component,
                'filearea' => 'content', 'itemid' => 0, 'filepath' => '/', 'filename' => $filename,
                'author' => 'Original teacher', 'license' => 'cc',
            ],
            $content
        );
    }

    /**
     * Create identical source editions and an existing destination folder.
     *
     * @param int $profileid Profileid.
     * @param string $uuid Uuid.
     * @return array Result.
     */
    private function folder_editions(int $profileid, string $uuid): array {
        $courses = [];
        foreach ([2024, 2025, 2026] as $edition) {
            $courses[$edition] = $this->datagenerator->create_course(
                [
                    'fullname' => 'FileBank ' . $edition, 'shortname' => 'CB' . $edition,
                    'idnumber' => $uuid . '||CB||' . $edition,
                ]
            );
            $folder = $this->datagenerator->create_module(
                'folder',
                [
                    'course' => $courses[$edition]->id, 'name' => $edition === 2026 ? 'Target materials' : 'Published materials',
                ]
            );
            $this->create_file(
                $folder->cmid,
                'mod_folder',
                $edition === 2026 ? 'existing.txt' : 'lesson.txt',
                $edition === 2026 ? 'Existing destination content' : 'Shared lesson content'
            );
            if ($edition !== 2026) {
                get_file_storage()->create_file_from_string(
                    [
                        'contextid' => context_module::instance($folder->cmid)->id, 'component' => 'mod_folder',
                        'filearea' => 'content', 'itemid' => 0, 'filepath' => '/notes/',
                        'filename' => 'notes' . $edition . '.txt', 'author' => 'Original teacher', 'license' => 'cc',
                    ],
                    'Notes from ' . $edition
                );
            }
            if ($edition === 2026) {
                $targetcmid = $folder->cmid;
            } else {
                material_source::publish($profileid, $folder->cmid, false);
            }
        }
        return [$courses, $targetcmid];
    }

    /**
     * Create public resource/assignment leaves and hidden/private exclusion fixtures.
     *
     * @param array $courses Courses.
     * @param int $profileid Profileid.
     */
    private function public_activities(array $courses, int $profileid): void {
        $resource = $this->datagenerator->create_module(
            'resource',
            [
                'course' => $courses[2025]->id, 'name' => 'Published resource',
            ]
        );
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
        $targetresource = $this->datagenerator->create_module(
            'resource',
            [
                'course' => $courses[2026]->id, 'name' => 'Target resource', 'display' => RESOURCELIB_DISPLAY_EMBED,
            ]
        );
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
        $assignment = $this->datagenerator->create_module(
            'assign',
            [
                'course' => $courses[2025]->id, 'name' => 'Public assignment handouts', 'alwaysshowdescription' => 1,
            ]
        );
        foreach (['introattachment' => 'handout.txt', 'submission_files' => 'student-private.txt'] as $area => $filename) {
            get_file_storage()->create_file_from_string(
                [
                    'contextid' => context_module::instance($assignment->cmid)->id,
                    'component' => $area === 'introattachment' ? 'mod_assign' : 'assignsubmission_file',
                    'filearea' => $area, 'itemid' => $area === 'introattachment' ? 0 : 1,
                    'filepath' => '/', 'filename' => $filename, 'author' => 'Original teacher', 'license' => 'cc',
                ],
                $filename
            );
        }
        material_source::publish($profileid, $assignment->cmid, false);
        $this->datagenerator->create_module(
            'url',
            ['course' => $courses[2026]->id,
                'name' => 'Target URL', 'externalurl' => 'https://example.org',
                'intro' => '<p>Existing description</p>', 'introformat' => FORMAT_HTML]
        );
        $hidden = $this->datagenerator->create_module(
            'folder',
            [
                'course' => $courses[2025]->id, 'name' => 'Hidden materials', 'visible' => 0,
            ]
        );
        $this->create_file($hidden->cmid, 'mod_folder', 'hidden.txt', 'Hidden content');
    }
}
