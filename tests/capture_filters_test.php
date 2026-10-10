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
 * Configurable native picker filters and persistent selection contracts.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank;

use local_filebank\local\profile\profiles;
use local_filebank\local\catalog\catalog;
use local_filebank\local\catalog\indexer;
use local_filebank\local\selection\selection_basket;
use local_filebank\local\selection\browser_listing;
use local_filebank\output\capture_filters;
use local_filebank\output\display;

/**

 * Exercises the real index, live authorization and native names.

 * @covers \local_filebank\local\profile\profiles
 * @covers \local_filebank\local\catalog\catalog
 * @covers \local_filebank\local\catalog\indexer
 * @covers \local_filebank\local\selection\selection_basket
 * @covers \local_filebank\local\selection\browser_listing
 * @covers \local_filebank\output\capture_filters
 * @covers \local_filebank\output\display
 */
final class capture_filters_test extends \advanced_testcase {
    /**
     * Guided fields retain unrelated advanced rules and produce named capture definitions.
     */
    public function test_guided_profile_document_preserves_advanced_rules(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $config = file_get_contents(__DIR__ . '/../examples/institutional-profile.json');
        $original = json_decode($config, true);
        $document = \local_filebank\form\profile_form::document([
            'configjson' => $config, 'profilename' => 'Guided profile', 'editmode' => 'guided',
            'coursefield' => 'idnumber', 'coursepattern' => $original['relationship']['parsers'][0]['pattern'],
            'familytemplate' => '${family}', 'filtercapture' => ['edition', ''],
            'filterlabel' => ['Academic year', ''], 'filteroptional' => [true, false],
        ]);
        $this->assertSame($original['migration'], $document['migration']);
        $this->assertSame($original['publication'], $document['publication']);
        $this->assertSame(
            ['edition' => ['capture' => 'edition', 'label' => 'Academic year', 'optional' => true]],
            $document['relationship']['filters']
        );
        $this->assertSame('Guided profile', $document['name']);
    }

    /**
     * Guided source checkboxes opt in to public handouts while preserving specific advanced intro rules.
     */
    public function test_guided_sources_preserve_unrepresented_areas(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $original = json_decode(file_get_contents(__DIR__ . '/../examples/institutional-profile.json'), true);
        $original['publication']['allowed_sources'] = ['mod_folder/content', 'mod_url/intro'];
        $document = \local_filebank\form\profile_form::document([
            'configjson' => json_encode($original), 'profilename' => 'Public handouts', 'editmode' => 'guided',
            'coursefield' => 'idnumber', 'coursepattern' => $original['relationship']['parsers'][0]['pattern'],
            'familytemplate' => '${family}', 'filtercapture' => [], 'filterlabel' => [],
            'sourcesdefined' => 1, 'sources' => [sha1('mod_assign/introattachment') => 1],
        ]);
        $this->assertEqualsCanonicalizing(
            ['mod_url/intro', 'mod_assign/introattachment'],
            $document['publication']['allowed_sources']
        );
        $this->assertSame($original['migration'], $document['migration']);
    }

    /**
     * A file search has bounded, distinct pages and preserves off-page selections.
     */
    public function test_picker_pages_keep_selection_without_repeating_files(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_folder_fixture(['name' => 'Paged materials']);
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        $target = $DB->get_record('course', ['shortname' => 'CB2026'], '*', MUST_EXIST);
        $folders = get_fast_modinfo($source)->get_instances_of('folder');
        $folder = reset($folders);
        $targets = get_fast_modinfo($target)->get_instances_of('folder');
        $destination = reset($targets);
        for ($i = 0; $i < 60; $i++) {
            get_file_storage()->create_file_from_string([
                'contextid' => $folder->context->id, 'component' => 'mod_folder', 'filearea' => 'content',
                'itemid' => 0, 'filepath' => '/', 'filename' => 'page-document-' . $i . '.txt',
                'author' => 'Original teacher', 'license' => 'cc',
            ], 'Unique page document ' . $i);
        }
        indexer::refresh($source->id);
        $scope = ['courseid' => $target->id, 'contextid' => $destination->context->id,
            'limits' => ['maxfiles' => -1]];
        $state = ['profileid' => $fixture['profileid'], 'filters' => ['q' => 'page-document-',
            'captures' => [], 'mimetype' => ''], 'after' => '', 'folder' => '', 'selected' => []];
        $first = browser_listing::page($scope, $state);
        $this->assertCount(50, $first['cards']);
        $this->assertNotEmpty($first['next']);
        $selected = $first['cards'][0]['value'];
        $state['after'] = $first['next'];
        $state['selected'] = [$selected];
        $second = browser_listing::page($scope, $state);
        $this->assertCount(10, $second['cards']);
        $this->assertSame('', $second['next']);
        $this->assertSame([['value' => $selected]], $second['retained']);
        $this->assertSame([], array_intersect(array_column($first['cards'], 'value'), array_column($second['cards'], 'value')));
        $this->assertSame(1, $second['count']);
        $this->assertSame(['2025' => '2025'], $second['suggestions']['edition']);
        $state['after'] = '';
        $again = browser_listing::page($scope, $state);
        $this->assertTrue($again['cards'][0]['checked']);
        $this->assertSame([], $again['retained']);
    }

    /**
     * Filters use arbitrary named captures, rather than a hard-coded edition column.
     */
    public function test_dynamic_capture_filters_and_live_refresh(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $config = json_decode(file_get_contents(__DIR__ . '/../examples/institutional-profile.json'), true);
        $config['relationship']['parsers'][0] = ['field' => 'idnumber',
            'pattern' => '~^(?<family>[^|]+)\|(?<year>\d{4})\|(?<semester>[12])$~'];
        $config['relationship']['display_label'] = '${family}';
        $config['relationship']['edition']['capture'] = 'year';
        $config['relationship']['filters'] = [
            'edition' => ['capture' => 'year', 'label' => 'Academic year', 'optional' => false],
            'semester' => ['capture' => 'semester', 'label' => 'Semester', 'optional' => false],
        ];
        $profileid = profiles::save(json_encode($config));
        $source = $this->getDataGenerator()->create_course(['idnumber' => 'science|2025|1']);
        $target = $this->getDataGenerator()->create_course(['idnumber' => 'science|2026|2']);
        $folder = $this->getDataGenerator()->create_module('folder', ['course' => $source->id]);
        get_file_storage()->create_file_from_string(['contextid' => \context_module::instance($folder->cmid)->id,
            'component' => 'mod_folder', 'filearea' => 'content', 'itemid' => 0, 'filepath' => '/',
            'filename' => 'shared.txt', 'author' => 'Original author', 'license' => 'cc'], 'shared content');
        indexer::refresh($source->id);
        indexer::refresh($target->id);
        $filters = ['captures' => ['semester' => '1', 'edition' => '2025']];
        $this->assertCount(1, catalog::search($target->id, $profileid, $filters)['assets']);
        $this->assertCount(0, catalog::search($target->id, $profileid, ['captures' => ['semester' => '2']])['assets']);
        $controls = capture_filters::data($profileid, ['semester' => '1']);
        $this->assertSame('Academic year', $controls[0]['label']);
        $this->assertSame('1', $controls[1]['value']);
        $this->assertSame(4, $DB->count_records('local_filebank_value'));
        $source->idnumber = 'science|2025|2';
        $DB->update_record('course', $source);
        // An old index entry cannot bypass the current course captures.
        $this->assertCount(0, catalog::search($target->id, $profileid, $filters)['assets']);
        indexer::refresh($source->id);
        $this->assertCount(1, catalog::search($target->id, $profileid, ['captures' => ['semester' => '2']])['assets']);
        $this->assertSame(4, $DB->count_records('local_filebank_value'));
    }

    /**

     * Unknown browser-supplied keys never become SQL identifiers.

     */
    public function test_unknown_capture_filter_is_rejected(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_folder_fixture(['name' => 'Filter authorization']);
        global $DB;
        $target = $DB->get_record('course', ['shortname' => 'CB2026'], '*', MUST_EXIST);
        $this->expectException(\invalid_parameter_exception::class);
        catalog::search($target->id, $fixture['profileid'], ['captures' => ['unknown' => 'value']]);
    }

    /**

     * Selected tokens can be removed after access changes without disclosing private names.

     */
    public function test_basket_rechecks_source_access(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_folder_fixture(['name' => 'Selection authorization']);
        $target = $DB->get_record('course', ['shortname' => 'CB2026'], '*', MUST_EXIST);
        $source = $DB->get_record('course', ['shortname' => 'CB2025'], '*', MUST_EXIST);
        $cm = get_fast_modinfo($source)->get_instances_of('folder');
        $cm = reset($cm);
        $value = 'folder:' . $cm->id . ':' . base64_encode('/');
        $rows = selection_basket::data($target->id, $fixture['profileid'], [$value]);
        $this->assertStringContainsString('Published materials', $rows[0]['title']);
        $DB->set_field('course_modules', 'visible', 0, ['id' => $cm->id]);
        rebuild_course_cache($source->id, true);
        $rows = selection_basket::data($target->id, $fixture['profileid'], [$value]);
        $this->assertSame(get_string('selectionunavailable', 'local_filebank'), $rows[0]['title']);
        $this->assertSame($value, $rows[0]['value']);
    }

    /**

     * Name filters run in Moodle's supplied context and do not expose executable HTML.

     */
    public function test_names_use_moodle_string_filters(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);
        $name = '<span lang="en" class="multilang">English name</span>'
            . '<span lang="es" class="multilang">Nombre español</span>';
        $this->assertSame('English name', trim(display::name($name, \context_system::instance())));
        $this->assertStringNotContainsString('<script', display::name('<script>alert(1)</script>Safe name'));
    }
}
