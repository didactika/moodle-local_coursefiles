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
 * Native output escaping and form semantics tests.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefiles;

use local_coursefiles\output\display;
use local_coursefiles\output\view;

/**
 * Exercises actual templates through Moodle's renderer.
 * @covers \local_coursefiles\output\display
 * @covers \local_coursefiles\output\view
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\output\display::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\local_coursefiles\output\view::class)]
final class output_test extends \advanced_testcase {
    /**
     * Personal action fields remain escaped and the log filters have labels.
     */
    public function test_log_escapes_records_and_labels_filters(): void {
        $renderer = $this->renderer();
        $html = $renderer->render(new view('log', [
            'hasrecords' => true, 'formurl' => '/local/coursefiles/admin/log.php',
            'entityuuid' => '" autofocus onfocus="alert(1)',
            'records' => [['date' => 'Today', 'action' => '<script>action</script>',
                'user' => '<script>user</script>', 'reference' => '<script>reference</script>']],
        ]));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;user&lt;/script&gt;', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//input[not(@type="hidden")] | //select') as $control) {
            $this->assertSame(1, $xpath->query('//label[@for="' . $control->getAttribute('id') . '"]')->length);
            $this->assertFalse($control->hasAttribute('onfocus'));
        }
    }

    /**
     * Prepare a native course renderer.
     *
     * @return \renderer_base Component renderer.
     */
    private function renderer(): \renderer_base {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_url('/local/coursefiles/course/index.php', ['courseid' => $course->id]);
        return $PAGE->get_renderer('local_coursefiles');
    }

    /**
     * Dynamic declarations are escaped and filter labels reference real controls.
     */
    public function test_catalog_escapes_origins_and_labels_controls(): void {
        $renderer = $this->renderer();
        $data = [
            'hasprofiles' => true, 'hasassets' => true, 'courseid' => 2,
            'actionurl' => '/local/coursefiles/course/index.php', 'reseturl' => '/local/coursefiles/course/index.php',
            'profiles' => display::options([1 => '<script>profile</script>'], '1'),
            'filters' => ['q' => '" autofocus onfocus="alert(1)'],
            'modules' => display::options(['' => 'All activities'], ''),
            'filetypes' => display::options(display::filetypes(), ''),
            'assets' => [[
                'filename' => '<script>file</script>.pdf', 'size' => '1 KB', 'origincount' => 1,
                'occurrences' => [[
                    'shortname' => 'BIO101', 'editionlabel' => '2025', 'modulename' => 'Class materials',
                    'filepath' => '/week1/', 'authorlabel' => '<script>author</script>', 'license' => 'cc',
                ]],
            ]],
        ];
        $html = $renderer->render(new view('catalog', $data));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;author&lt;/script&gt;', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//input[not(@type="hidden")] | //select') as $control) {
            $id = $control->getAttribute('id');
            $this->assertSame(1, $xpath->query('//label[@for="' . $id . '"]')->length);
        }
    }

    /**
     * Site-controlled locked policy explains refusal without editable controls.
     */
    public function test_locked_policy_has_clear_effective_state(): void {
        $renderer = $this->renderer();
        $html = $renderer->render(new view('policy', [
            'locked' => true, 'canoverride' => false,
            'effectivelabel' => get_string('preserve_locked', 'local_coursefiles'),
        ]));
        $this->assertStringContainsString(get_string('lockedpolicyhelp', 'local_coursefiles'), $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    /**
     * Reports show named destinations and translated outcomes.
     */
    public function test_migration_report_uses_readable_decisions(): void {
        $renderer = $this->renderer();
        $html = $renderer->render(new view('migration_report', [
            'planstate' => display::outcome('reviewable'), 'sourceedition' => '2025',
            'targetedition' => '2026', 'reviewstep' => true,
            'hasoperations' => true, 'courses' => [[
                'sourcecoursehtml' => display::name('BIO101-2025'), 'targetcoursehtml' => display::name('BIO101-2026'),
                'folders' => [['targetsectionhtml' => display::name('Week 1'),
                'targetfolderhtml' => display::name('Class materials'), 'operations' => [[
                'filename' => 'lesson.pdf', 'filepath' => '/week1/', 'author' => 'Original author', 'license' => 'cc',
                'decisionlabel' => display::outcome('skip_content'), 'statelabel' => display::outcome('pending'),
                ]]]],
            ]],
        ]));
        $this->assertStringContainsString('BIO101-2026', $html);
        $this->assertStringContainsString(get_string('outcome_skip_content', 'local_coursefiles'), $html);
        $this->assertStringNotContainsString('<pre', $html);
        $this->assertStringNotContainsString('skip_content', $html);
    }
}
