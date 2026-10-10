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
 * Permission-aware page navigation.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\output;

/**
 * Defines the administration entry points and course tabs.
 */
final class navigation {
    /**
     * Open the first permitted page directly from the native plugin category.
     *
     * @param \core\hook\output\before_http_headers|null $hook Native output hook; null on legacy cores.
     */
    public static function open_category(?\core\hook\output\before_http_headers $hook = null): void {
        global $PAGE, $CFG;
        static $redirecting = false;
        if (
            CLI_SCRIPT || AJAX_SCRIPT || $redirecting
                || $PAGE->url->get_path() !== '/' . $CFG->admin . '/category.php'
                || $PAGE->url->get_param('category') !== 'local_filebank'
        ) {
            return;
        }
        foreach (self::pages() as $name => $page) {
            if ($name !== 'log' && has_capability($page['capability'], \context_system::instance())) {
                $redirecting = true;
                redirect(new \moodle_url('/local/filebank/admin/' . $name . '.php'));
            }
        }
    }

    /**
     * Return registered administration pages.
     *
     * @return array Page contracts.
     */
    public static function pages(): array {
        return [
            'settings' => ['label' => 'settings', 'capability' => 'moodle/site:config'],
            'profiles' => ['label' => 'profiles', 'capability' => 'local/filebank:manageprofiles'],
            'migration' => ['label' => 'migrations', 'capability' => 'local/filebank:planmigration'],
            'log' => ['label' => 'log', 'capability' => 'local/filebank:viewlog'],
        ];
    }

    /**
     * Prepare visible site tabs.
     *
     * @param string $active Current page.
     * @return view Navigation view.
     */
    public static function site(string $active): view {
        $tabs = [];
        foreach (self::pages() as $name => $page) {
            if (has_capability($page['capability'], \context_system::instance())) {
                $tabs[] = ['label' => get_string($page['label'], 'local_filebank'),
                    'url' => (new \moodle_url('/local/filebank/admin/' . $name . '.php'))->out(false),
                    'active' => $name === $active];
            }
        }
        return new view('navigation', ['tabs' => $tabs]);
    }

    /**
     * Prepare visible course tabs without exposing site administration.
     *
     * @param string $active Current page.
     * @param int $courseid Course.
     * @return view Navigation view.
     */
    public static function course(string $active, int $courseid): view {
        $tabs = [];
        foreach (['index' => ['dashboard', 'browse'], 'policy' => ['coursepolicy', 'managecoursepolicy']] as $name => $page) {
            if (has_capability('local/filebank:' . $page[1], \context_course::instance($courseid))) {
                $tabs[] = ['label' => get_string($page[0], 'local_filebank'),
                    'url' => (new \moodle_url(
                        '/local/filebank/course/' . $name . '.php',
                        ['courseid' => $courseid]
                    ))->out(false), 'active' => $name === $active];
            }
        }
        return new view('navigation', ['tabs' => $tabs]);
    }
}
