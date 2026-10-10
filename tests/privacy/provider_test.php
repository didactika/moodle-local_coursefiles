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
 * Approved privacy export and deletion integration tests.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Checks actual actor records, native files and approved context boundaries.
 * @covers \local_multipicker\privacy\provider
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\privacy\provider::class)]
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Exports include every actor table and exclude the other actor's records.
     */
    public function test_export_is_limited_to_approved_actor(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $records = $this->create_records($actor->id);
        $this->create_records($other->id);
        $system = \context_system::instance();
        provider::export_user_data(new approved_contextlist($actor, 'local_multipicker', [$system->id]));
        $writer = writer::with_context($system);
        foreach ($records as $suffix => $record) {
            $data = $writer->get_data([get_string('pluginname', 'local_multipicker'), $suffix]);
            $this->assertEquals([$record], $data->records);
        }
        $this->assertEquals([$system->id], provider::get_contexts_for_userid($actor->id)->get_contextids());
    }

    /**
     * Course contexts and empty approvals cannot export or delete system records.
     */
    public function test_unapproved_contexts_are_ignored(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $records = $this->create_records($actor->id);
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        foreach ([[], [$context->id]] as $ids) {
            $approved = new approved_contextlist($actor, 'local_multipicker', $ids);
            provider::export_user_data($approved);
            provider::delete_data_for_user($approved);
        }
        provider::delete_data_for_users(new approved_userlist($context, 'local_multipicker', [$actor->id]));
        provider::delete_data_for_all_users_in_context($context);
        $this->assertFalse(writer::with_context(\context_system::instance())->has_any_data_in_any_context());
        $this->assert_records_unchanged($records);
    }

    /**
     * Discovery returns unique actors and excludes removed identity and unrelated contexts.
     */
    public function test_discovery_excludes_unlinked_records(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $absent = $this->getDataGenerator()->create_user();
        $this->create_records($actor->id);
        $this->create_records($other->id);
        $this->create_records(0);
        $users = new userlist(\context_system::instance(), 'local_multipicker');
        provider::get_users_in_context($users);
        $this->assertEqualsCanonicalizing([$actor->id, $other->id], $users->get_userids());
        $this->assertEmpty(provider::get_contexts_for_userid($absent->id)->get_contextids());
        $course = $this->getDataGenerator()->create_course();
        $users = new userlist(\context_course::instance($course->id), 'local_multipicker');
        provider::get_users_in_context($users);
        $this->assertEmpty($users->get_userids());
    }

    /**
     * Bulk deletion cancels selected runs and preserves unrelated users and native material.
     */
    public function test_bulk_deletion_preserves_unapproved_records_and_files(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $retained = $this->getDataGenerator()->create_user();
        $first = $this->create_records($actor->id);
        $second = $this->create_records($other->id);
        $unchanged = $this->create_records($retained->id);
        $system = \context_system::instance();
        $file = get_file_storage()->create_file_from_string([
            'contextid' => \context_module::instance($first['publication']->cmid)->id,
            'component' => 'mod_folder', 'filearea' => 'content',
            'itemid' => 0, 'filepath' => '/', 'filename' => 'material.txt',
            'userid' => $actor->id, 'author' => 'Independent declared author', 'license' => 'cc',
        ], 'Teaching material');
        provider::delete_data_for_users(new approved_userlist($system, 'local_multipicker', []));
        $this->assert_records_unchanged($first);
        provider::delete_data_for_users(new approved_userlist($system, 'local_multipicker', [$actor->id, $other->id]));
        $this->assert_records_erased($first);
        $this->assert_records_erased($second);
        $this->assert_records_unchanged($unchanged);
        $saved = get_file_storage()->get_file_by_id($file->get_id());
        $this->assertSame('Teaching material', $saved->get_content());
        $this->assertSame('Independent declared author', $saved->get_author());
        $this->assertEquals($actor->id, $saved->get_userid());
        $this->assertEmpty(provider::get_contexts_for_userid($actor->id)->get_contextids());
        $users = new userlist($system, 'local_multipicker');
        provider::get_users_in_context($users);
        $this->assertEquals([$retained->id], $users->get_userids());
    }

    /**
     * A single approved actor receives the same cancellation and unlinking behavior.
     */
    public function test_single_deletion_cancels_runs_and_preserves_lineage(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $records = $this->create_records($actor->id);
        provider::delete_data_for_user(new approved_contextlist(
            $actor,
            'local_multipicker',
            [\context_system::instance()->id]
        ));
        $this->assert_records_erased($records);
    }

    /**
     * System-wide deletion also removes system log rows and cancels unlinked runs.
     */
    public function test_system_deletion_includes_unlinked_records(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $records = $this->create_records($actor->id);
        $unlinked = $this->create_records(0);
        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assert_records_erased($records);
        $this->assert_records_erased($unlinked);
    }

    /**
     * Seed persisted actor records with independent author and lineage declarations.
     *
     * @param int $userid Technical actor.
     * @return array Records indexed by table suffix.
     */
    private function create_records(int $userid): array {
        global $DB;
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $course->id]);
        $uuid = \core\uuid::generate();
        $profileid = $DB->insert_record('local_multipicker', [
            'uuid' => $uuid, 'name' => 'Privacy fixture', 'activeid' => 0, 'enabled' => 1,
        ]);
        $rows = [
            'revision' => [
                'profileid' => $profileid, 'revision' => 1, 'configjson' => '{}', 'digest' => hash('sha256', $uuid),
                'timecreated' => time(),
            ],
            'coursepolicy' => [
                'courseid' => $course->id, 'policy' => 'preserve_editable', 'revision' => 1, 'timemodified' => time(),
            ],
            'publication' => ['profileid' => $profileid, 'cmid' => $folder->cmid, 'timecreated' => time(), 'revoked' => 0],
            'attribution' => [
                'uuid' => $uuid, 'parentuuid' => '', 'sourcehash' => sha1($uuid), 'contenthash' => sha1('material'),
                'author' => 'Independent declared author', 'license' => 'cc', 'locked' => 0, 'timecreated' => time(),
            ],
            'origin' => ['pathnamehash' => sha1($uuid), 'contenthash' => sha1('material'), 'timecreated' => time()],
            'plan' => [
                'uuid' => $uuid, 'profileid' => $profileid, 'configjson' => '{}', 'digest' => hash('sha256', $uuid),
                'state' => 'reviewable', 'timecreated' => time(),
            ],
            'run' => ['state' => 'queued', 'cancelled' => 0, 'timecreated' => time(), 'timemodified' => time()],
            'log' => ['action' => 'migration_batch', 'entityuuid' => $uuid, 'details' => '{"batch":1}', 'timecreated' => time()],
        ];
        $records = [];
        foreach ($rows as $suffix => $row) {
            if ($suffix === 'origin') {
                $row['attributionid'] = $records['attribution']->id;
            } else if ($suffix === 'plan') {
                $row['revisionid'] = $records['revision']->id;
            } else if ($suffix === 'run') {
                $row['planid'] = $records['plan']->id;
            }
            $row['userid'] = $userid;
            $id = $DB->insert_record('local_multipicker_' . $suffix, $row);
            $records[$suffix] = $DB->get_record('local_multipicker_' . $suffix, ['id' => $id], '*', MUST_EXIST);
        }
        return $records;
    }

    /**
     * Verify deletion changes only actor links and cancellation, removing complete log rows.
     *
     * @param array $records Original records.
     */
    private function assert_records_erased(array $records): void {
        global $DB;
        foreach ($records as $suffix => $record) {
            $current = $DB->get_record('local_multipicker_' . $suffix, ['id' => $record->id]);
            if ($suffix === 'log') {
                $this->assertFalse($current);
                continue;
            }
            $expected = clone $record;
            $expected->userid = 0;
            if ($suffix === 'run') {
                $expected->cancelled = 1;
            }
            $this->assertEquals($expected, $current);
        }
    }

    /**
     * Verify all persisted fields remain unchanged for unapproved actors or contexts.
     *
     * @param array $records Original records.
     */
    private function assert_records_unchanged(array $records): void {
        global $DB;
        foreach ($records as $suffix => $record) {
            $this->assertEquals($record, $DB->get_record('local_multipicker_' . $suffix, ['id' => $record->id]));
        }
    }
}
