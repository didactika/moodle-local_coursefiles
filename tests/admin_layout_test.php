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
 * Administration authorization and bounded review tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\form\profile_form;
use local_filebank\local\migration\view\migration_decisions;
use local_filebank\local\migration\view\migration_files;
use local_filebank\local\migration\view\migration_overview;
use local_filebank\local\profile\profiles;
use local_filebank\output\navigation;

/**
 * Tests the real presentation contracts and permissions.
 *
 * @covers \local_filebank\local\migration\view\migration_overview
 * @covers \local_filebank\local\migration\view\migration_files
 * @covers \local_filebank\local\migration\view\migration_decisions
 * @covers \local_filebank\output\navigation
 * @covers \local_filebank\form\profile_form
 * @covers \local_filebank\output\profile_summary
 */
final class admin_layout_test extends \advanced_testcase {
    /**
     * A delegated profile manager sees only the permitted administration tab.
     */
    public function test_tabs_follow_system_permissions(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $roleid = create_role('Profile manager', 'profilemanager', '');
        assign_capability('local/filebank:manageprofiles', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        $this->setUser($user);
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/local/filebank/admin/profiles.php');
        $tabs = navigation::site('profiles')->export_for_template($PAGE->get_renderer('core'));
        $this->assertCount(1, $tabs['tabs']);
        $this->assertTrue($tabs['tabs'][0]['active']);
        $this->assertStringContainsString('/admin/profiles.php', $tabs['tabs'][0]['url']);
        $this->expectException(\required_capability_exception::class);
        migration_overview::get(1);
    }

    /**
     * Unsaved editor preview validates rules without activating a revision.
     */
    public function test_profile_preview_does_not_write(): void {

        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $json = json_encode($this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config());
        $document = profile_form::document(['configjson' => $json, 'profilename' => 'Preview only']);
        $this->assertSame('Preview only', $document['name']);
        $this->assertSame(0, $DB->count_records('local_filebank_revision'));
        $summary = \local_filebank\output\profile_summary::data($document, false);
        $html = $GLOBALS['OUTPUT']->render_from_template('local_filebank/local/profile/summary', $summary);
        $this->assertStringNotContainsString(get_string('rulesinuse', 'local_filebank'), $html);
        $this->assertStringContainsString('Preview only', $html);
        $this->expectException(\moodle_exception::class);
        profile_form::document(['configjson' => 'null', 'profilename' => 'Invalid']);
    }

    /**
     * Group counts, filters and pages use the complete stored migration, and what waits for a decision is listed apart.
     */
    public function test_pages_preserve_revision_and_whole_migration_counts(): void {

        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $config = $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
        $profileid = profiles::save(json_encode($config));
        [, $revision] = profiles::load($profileid);
        $document = json_encode(['sourceedition' => '2025', 'targetedition' => '2026', 'issues' => []]);
        $planid = $DB->insert_record(
            'local_filebank_plan',
            (object)[
                'uuid' => \core\uuid::generate(), 'profileid' => $profileid, 'revisionid' => $revision->id,
                'userid' => $USER->id, 'configjson' => $document, 'digest' => hash('sha256', $document),
                'state' => 'reviewable', 'timecreated' => time(),
            ]
        );
        $source = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();
        for ($index = 0; $index < 58; $index++) {
            $DB->insert_record(
                'local_filebank_operation',
                (object)[
                    'planid' => $planid, 'opkey' => hash('sha256', (string)$index), 'sourcehash' => sha1((string)$index),
                    'targetcmid' => 999, 'sourcecourseid' => $source->id, 'targetcourseid' => $target->id,
                    'sourcecmid' => 7, 'included' => $index < 57 ? 1 : 0,
                    'decision' => $index < 55 ? 'copy' : ($index < 57 ? 'skip_content' : 'blocked_content'),
                    'state' => $index === 0 ? 'copied' : 'pending', 'result' => '{}', 'timemodified' => time(),
                    'snapshot' => json_encode(
                        ['sourcecourse' => $source->id, 'sourcecmid' => 7, 'sourcefoldername' => 'Published',
                            'source' => ['filename' => 'file' . $index . '.txt', 'filepath' => '/',
                                'author' => 'Original', 'license' => 'cc'],
                            'target' => ['courseid' => $target->id, 'name' => 'Materials',
                            'sectionname' => 'Topic', 'sectionnum' => 1]]
                    ),
                ]
            );
        }
        $config['migration']['source_module']['equals'] = 'Changed after preview';
        profiles::save(json_encode($config), $profileid);
        $overview = migration_overview::get($planid);
        $this->assertSame(58, $overview['summary']['files']);
        $this->assertSame(55, $overview['summary']['tocopy']);
        $this->assertSame(2, $overview['summary']['existing']);
        $this->assertSame(1, $overview['summary']['conflicts']);
        $this->assertFalse($overview['canexecute'], 'A migration of an older revision of the profile cannot be started');
        $this->assertSame('blocked_content', $overview['natures'][0]['nature']);
        $this->assertSame(1, $overview['natures'][0]['count']);
        $this->assertSame(['skip', 'overwrite'], array_column($overview['natures'][0]['choices'], 'value'));
        $all = migration_files::page($planid, '', '', 0, 25);
        $this->assertSame(58, $all['total']);
        $this->assertCount(25, $all['files']);
        $this->assertSame(55, $all['counts']['copy']);
        $last = migration_files::page($planid, 'copy', '', 2, 25);
        $this->assertCount(5, $last['files']);
        $existing = migration_files::page($planid, 'existing', '', 0, 25);
        $this->assertSame(2, $existing['total']);
        $search = migration_files::page($planid, '', 'file57.txt', 0, 25);
        $this->assertSame(1, $search['total']);
        $this->assertSame('file57.txt', $search['files'][0]['filename']);
        $this->assertFalse($search['files'][0]['settable'], 'What waits for a decision is settled, not just checked');
        $folders = migration_files::folders($planid, '', 0, 25);
        $this->assertSame(1, $folders['total']);
        $this->assertSame(58, $folders['folders'][0]['total']);
        $this->assertSame(57, $folders['folders'][0]['included']);
        $decisions = migration_decisions::page($planid, 'file', 'blocked_content', 0, 25);
        $this->assertSame(1, $decisions['total']);
        $this->assertSame('file57.txt', $decisions['items'][0]['label']);
        $document = json_encode(
            ['sourceedition' => '2025', 'targetedition' => '2026',
                'issues' => array_fill(0, 61, ['reason' => 'ambiguous_course_pair'])]
        );
        $DB->update_record(
            'local_filebank_plan',
            (object)[
                'id' => $planid, 'configjson' => $document, 'digest' => hash('sha256', $document),
            ]
        );
        $issues = migration_decisions::page($planid, 'issue', 'ambiguous_course_pair', 1, 50);
        $this->assertSame(61, $issues['total']);
        $this->assertCount(11, $issues['items']);
        $this->assertSame(['exclude'], array_column($issues['items'][0]['choices'], 'value'));
    }
}
