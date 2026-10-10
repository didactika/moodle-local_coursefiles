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
 * Administration authorization and bounded review tests.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker;

use local_multipicker\form\profile_form;
use local_multipicker\local\profile\profiles;
use local_multipicker\output\migration_report;
use local_multipicker\output\navigation;

/**
 * Tests the real presentation contracts and permissions.
 *
 * @covers \local_multipicker\output\migration_report
 * @covers \local_multipicker\output\navigation
 * @covers \local_multipicker\form\profile_form
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\output\migration_report::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\output\navigation::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_multipicker\form\profile_form::class)]
final class admin_layout_test extends \advanced_testcase {
    /**
     * A delegated profile manager sees only the permitted administration tab.
     */
    public function test_tabs_follow_system_permissions(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $roleid = create_role('Profile manager', 'profilemanager', '');
        assign_capability('local/multipicker:manageprofiles', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        $this->setUser($user);
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/local/multipicker/admin/profiles.php');
        $tabs = navigation::site('profiles')->export_for_template($PAGE->get_renderer('core'));
        $this->assertCount(1, $tabs['tabs']);
        $this->assertTrue($tabs['tabs'][0]['active']);
        $this->assertStringContainsString('/admin/profiles.php', $tabs['tabs'][0]['url']);
        $this->expectException(\required_capability_exception::class);
        migration_report::data(1);
    }

    /**
     * Unsaved editor preview validates rules without activating a revision.
     */
    public function test_profile_preview_does_not_write(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $json = file_get_contents($CFG->dirroot . '/local/multipicker/examples/institutional-profile.json');
        $document = profile_form::document(['configjson' => $json, 'profilename' => 'Preview only']);
        $this->assertSame('Preview only', $document['name']);
        $this->assertSame(0, $DB->count_records('local_multipicker_revision'));
        $this->expectException(\moodle_exception::class);
        profile_form::document(['configjson' => 'null', 'profilename' => 'Invalid']);
    }

    /**
     * Decision filtering and page counts use the complete immutable preview.
     */
    public function test_review_pages_preserve_revision_and_whole_plan_counts(): void {
        global $CFG, $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $config = json_decode(file_get_contents($CFG->dirroot . '/local/multipicker/examples/institutional-profile.json'), true);
        $profileid = profiles::save(json_encode($config));
        [$profile, $revision] = profiles::load($profileid);
        $document = json_encode(['sourceedition' => '2025', 'targetedition' => '2026', 'issues' => []]);
        $planid = $DB->insert_record('local_multipicker_plan', (object)[
            'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revision->id,
            'userid' => $USER->id, 'configjson' => $document, 'digest' => hash('sha256', $document),
            'state' => 'blocked', 'timecreated' => time(),
        ]);
        $source = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();
        for ($index = 0; $index < 58; $index++) {
            $DB->insert_record('local_multipicker_operation', (object)[
                'planid' => $planid, 'opkey' => hash('sha256', (string)$index), 'sourcehash' => sha1((string)$index),
                'targetcmid' => 999, 'decision' => $index < 55 ? 'copy' : ($index < 57 ? 'skip_content' : 'blocked_path'),
                'state' => $index === 0 ? 'copied' : 'pending', 'result' => '{}', 'timemodified' => time(),
                'snapshot' => json_encode(['sourcecourse' => $source->id,
                    'source' => ['filename' => 'file' . $index . '.txt', 'filepath' => '/',
                        'author' => 'Original', 'license' => 'cc'],
                    'target' => ['courseid' => $target->id, 'name' => 'Materials', 'sectionname' => 'Topic', 'sectionnum' => 1]]),
            ]);
        }
        $config['migration']['source_module']['equals'] = 'Changed after preview';
        profiles::save(json_encode($config), $profileid);
        $data = migration_report::data($planid);
        $this->assertSame([55, 2, 1], array_column($data['metrics'], 'count'));
        $this->assertCount(50, $data['groups'][0]['operations']);
        $this->assertSame(1, $data['plancompleted']);
        $this->assertSame(58, $data['plantotal']);
        $this->assertFalse($data['canexecute']);
        $this->assertStringContainsString('materiales_de_clase.module', $data['summary']['rows'][0]['value']);
        $second = migration_report::data($planid, 'copy', 1);
        $this->assertCount(5, $second['groups'][0]['operations']);
        $this->assertArrayHasKey('previousurl', $second);
        $this->assertArrayNotHasKey('nexturl', $second);
        $skips = migration_report::data($planid, 'skip');
        $this->assertCount(2, $skips['groups'][0]['operations']);
        $this->assertSame(58, $skips['plantotal']);
        $this->assertSame(1, (int)$skips['summary']['revision']);
        $search = migration_report::data($planid, '', 0, 'file57.txt');
        $this->assertCount(1, $search['groups'][0]['operations']);
        $this->assertSame('file57.txt', $search['groups'][0]['operations'][0]['filename']);
        $document = json_encode(['sourceedition' => '2025', 'targetedition' => '2026',
            'issues' => array_fill(0, 61, ['reason' => 'ambiguous_course_pair'])]);
        $DB->update_record('local_multipicker_plan', (object)[
            'id' => $planid, 'configjson' => $document, 'digest' => hash('sha256', $document),
        ]);
        $firstissues = migration_report::data($planid);
        $this->assertCount(50, $firstissues['issues']);
        $this->assertArrayHasKey('issuenexturl', $firstissues);
        $lastissues = migration_report::data($planid, '', 0, '', false, 999);
        $this->assertCount(11, $lastissues['issues']);
        $this->assertSame(1, $lastissues['issuepage']);
        $this->assertArrayHasKey('issuepreviousurl', $lastissues);
        $this->assertArrayNotHasKey('issuenexturl', $lastissues);
        $this->assertCount(50, $lastissues['groups'][0]['operations']);
    }
}
