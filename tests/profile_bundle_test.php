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
 * Configuration transport and guided editing regression tests.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank;

use local_filebank\form\profile_form;
use local_filebank\local\profile\guided_config;
use local_filebank\local\profile\profile_bundle;
use local_filebank\local\profile\profiles;

/**
 * Exercises exported configurations rather than copying site database identities.
 *
 * @covers \local_filebank\local\profile\profile_bundle
 * @covers \local_filebank\local\profile\guided_config
 */
final class profile_bundle_test extends \advanced_testcase {
    /**
     * Guided edits use server-loaded rules even when a posted document is invalid.
     */
    public function test_guided_form_uses_authoritative_base(): void {
        $config = $this->config();
        $data = guided_config::defaults($config) + [
            'editmode' => 'guided', 'profilename' => 'Guided rules', 'configjson' => 'untrusted invalid document',
            'coursefield' => 'idnumber', 'coursepattern' => $config['relationship']['parsers'][0]['pattern'],
            'familytemplate' => '${family}', 'guidedsettings' => 1,
        ];
        $document = profile_form::document($data, $config);
        $this->assertSame('Guided rules', $document['name']);
        $this->assertSame($config['migration']['source_module'], $document['migration']['source_module']);
        $this->assertSame($config['publication'], $document['publication']);
    }

    /**
     * Selected and complete exports round-trip as independent records.
     */
    public function test_selected_and_all_exports_import_new_profiles(): void {
        global $DB;
        $config = $this->config();
        $a = profiles::save(json_encode($config));
        $config['name'] = 'Another group';
        $b = profiles::save(json_encode($config));
        $selected = profile_bundle::export([$b]);
        $this->assertCount(1, profile_bundle::documents($selected));
        $all = profile_bundle::export([], true);
        $ids = profile_bundle::import($all);
        $this->assertCount(2, $ids);
        $this->assertEmpty(array_intersect([$a, $b], $ids));
        $this->assertSame(4, $DB->count_records('local_filebank'));
        $this->assertSame(4, $DB->count_records('local_filebank_revision'));
    }

    /**
     * A later invalid document prevents every write in an imported bundle.
     */
    public function test_invalid_bundle_does_not_partially_import(): void {
        global $DB;
        $config = $this->config();
        try {
            profile_bundle::import(json_encode(['format' => 'filebank-profiles', 'version' => 1,
                'profiles' => [$config, ['name' => 'Invalid rules']]]));
            $this->fail('Invalid documents must be rejected before writes.');
        } catch (\moodle_exception $exception) {
            $this->assertSame(0, $DB->count_records('local_filebank'));
            $this->assertSame(0, $DB->count_records('local_filebank_revision'));
        }
    }

    /**
     * Configuration transport requires the profile-management capability.
     */
    public function test_export_requires_permission(): void {
        $this->config();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        profile_bundle::export([], true);
    }

    /**
     * Native upload validation cannot read another actor's draft.
     */
    public function test_upload_validation_is_actor_bound(): void {
        global $USER;
        $config = $this->config();
        $draftid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id, 'component' => 'user', 'filearea' => 'draft',
            'itemid' => $draftid, 'filepath' => '/', 'filename' => 'profiles.json',
        ], json_encode($config));
        $this->assertCount(1, profile_bundle::documents(\local_filebank\form\profile_import_form::uploaded_document($draftid)));
        $other = $this->getDataGenerator()->create_user();
        $role = $this->getDataGenerator()->create_role();
        assign_capability('local/filebank:manageprofiles', CAP_ALLOW, $role, \context_system::instance()->id);
        role_assign($role, $other->id, \context_system::instance()->id);
        $this->setUser($other);
        $this->expectException(\moodle_exception::class);
        \local_filebank\form\profile_import_form::uploaded_document($draftid);
    }

    /**
     * Reset state and load a valid institutional configuration.
     *
     * @return array Configuration.
     */
    private function config(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        return $this->getDataGenerator()->get_plugin_generator('local_filebank')->create_profile_config();
    }
}
