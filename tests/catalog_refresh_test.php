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
 * Catalogue availability across activated profile revisions.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles;

use local_coursefiles\local\catalog\catalog;
use local_coursefiles\local\profile\profiles;
use local_coursefiles\task\refresh_profile;

/**
 * Exercises immediate metadata reuse and bounded background reconciliation.
 *
 * @covers \local_coursefiles\local\profile\profiles
 * @covers \local_coursefiles\local\catalog\catalog
 * @covers \local_coursefiles\task\refresh_profile
 */
final class catalog_refresh_test extends \advanced_testcase {
    /**
     * Renaming keeps materials available, while publication edits immediately restrict access.
     */
    public function test_presentation_edits_reuse_metadata_without_bypassing_publication(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_coursefiles')
            ->create_folder_fixture(['name' => 'Available catalogue']);
        [, , $rules] = profiles::load($fixture['profileid']);
        $config = $rules->get_config();
        $before = catalog::search($fixture['targetcourse'], $fixture['profileid']);
        $this->assertNotEmpty($before['assets']);
        $files = $DB->get_records_menu('local_coursefiles_file', [], 'id', 'id, pathnamehash');
        $config['name'] = 'Renamed catalogue';
        profiles::save(json_encode($config), $fixture['profileid']);
        $this->assertSame($before, catalog::search($fixture['targetcourse'], $fixture['profileid']));
        $this->assertSame($files, $DB->get_records_menu('local_coursefiles_file', [], 'id', 'id, pathnamehash'));
        $config['publication']['allowed_sources'] = [];
        profiles::save(json_encode($config), $fixture['profileid']);
        $this->assertEmpty(catalog::search($fixture['targetcourse'], $fixture['profileid'])['assets']);
    }

    /**
     * Changing capture keys is rebuilt automatically and the old queued revision cannot overwrite it.
     */
    public function test_changed_filters_queue_a_current_revision_refresh(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_coursefiles')
            ->create_folder_fixture(['name' => 'Changed captures']);
        [, $oldrevision, $rules] = profiles::load($fixture['profileid']);
        $config = $rules->get_config();
        $config['relationship']['filters'] = ['period' => ['capture' => 'edition', 'label' => 'Period', 'optional' => true]];
        profiles::save(json_encode($config), $fixture['profileid']);
        [, $revision] = profiles::load($fixture['profileid']);
        $this->assertEmpty(catalog::search($fixture['targetcourse'], $fixture['profileid'])['assets']);
        $queued = array_filter(
            $DB->get_records('task_adhoc', ['classname' => '\\local_coursefiles\\task\\refresh_profile']),
            static fn($record) => json_decode($record->customdata)->revisionid === (int)$revision->id
        );
        $this->assertCount(1, $queued);
        $task = new refresh_profile();
        $task->set_custom_data(json_decode(reset($queued)->customdata));
        $task->execute();
        $this->assertNotEmpty(catalog::search(
            $fixture['targetcourse'],
            $fixture['profileid'],
            ['captures' => ['period' => '2025']]
        )['assets']);
        $task->set_custom_data(['profileid' => $fixture['profileid'], 'revisionid' => (int)$oldrevision->id, 'after' => 0]);
        $task->execute();
        $this->assertFalse($DB->record_exists('local_coursefiles_course', ['revisionid' => $oldrevision->id]));
        $this->assertTrue($DB->record_exists('local_coursefiles_value', ['revisionid' => $revision->id, 'name' => 'period']));
    }

    /**
     * A large site is visited through bounded successor tasks rather than inside the save request.
     */
    public function test_refresh_continues_after_the_first_course_slice(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_coursefiles')
            ->create_folder_fixture(['name' => 'Large catalogue']);
        for ($i = 0; $i < 25; $i++) {
            $this->getDataGenerator()->create_course();
        }
        $target = get_course($fixture['targetcourse']);
        $last = $this->getDataGenerator()->create_course(['idnumber' => preg_replace('/2026$/', '2027', $target->idnumber)]);
        [, $revision] = profiles::load($fixture['profileid']);
        $task = new refresh_profile();
        $task->set_custom_data(['profileid' => $fixture['profileid'], 'revisionid' => (int)$revision->id, 'after' => 0]);
        $task->execute();
        $this->assertFalse($DB->record_exists('local_coursefiles_course', ['courseid' => $last->id]));
        $successors = array_filter(
            $DB->get_records('task_adhoc', ['classname' => '\\local_coursefiles\\task\\refresh_profile']),
            static fn($record) => json_decode($record->customdata)->after > 0
        );
        $this->assertCount(1, $successors);
        $task->set_custom_data(json_decode(reset($successors)->customdata));
        $task->execute();
        $this->assertTrue($DB->record_exists(
            'local_coursefiles_course',
            ['courseid' => $last->id, 'revisionid' => $revision->id]
        ));
    }
}
