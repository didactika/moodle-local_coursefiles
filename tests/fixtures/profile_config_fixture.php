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
 * Relationship and policy integration tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank;

use local_filebank\local\profile\profile_config;
use local_filebank\local\profile\profiles;
use local_filebank\local\catalog\indexer;
use local_filebank\local\catalog\catalog;
use local_filebank\local\migration\copy_decision;
use local_filebank\local\migration\migration_planner;
use local_filebank\local\migration\migration_executor;

/**
 * Shared native fixture setup for focused acceptance contracts.
 */
abstract class profile_config_fixture extends \advanced_testcase {
    /**
     * Build the matching test configuration.
     *
     * @return array Document.
     */
    protected function config(): array {
        return $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
    }

    /**
     * Create annual folder materials.
     *
     * @return array Profile, source, target, source folder, target folder.
     */
    protected function fixture(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        $profileid = profiles::save(json_encode($this->config()));
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $source = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MAT101||2025']);
        $target = $this->getDataGenerator()->create_course(['idnumber' => $uuid . '||MAT101||2026']);
        $sfolder = $this->getDataGenerator()->create_module(
            'folder',
            [
                'course' => $source->id, 'name' => 'materiales_de_clase.module', 'section' => 1]
        );
        $tfolder = $this->getDataGenerator()->create_module(
            'folder',
            [
                'course' => $target->id, 'name' => 'materiales_de_clase.module', 'section' => 1]
        );
        get_file_storage()->create_file_from_string(
            [
                'contextid' => \context_module::instance($sfolder->cmid)->id, 'component' => 'mod_folder',
                'filearea' => 'content', 'itemid' => 0, 'filepath' => '/week1/', 'filename' => 'slides.pdf',
                'author' => 'Original author', 'license' => 'cc',
            ],
            'identical teaching content'
        );
        indexer::refresh($source->id);
        indexer::refresh($target->id);
        return [$profileid, $source, $target, $sfolder, $tfolder];
    }
}
