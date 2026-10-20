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
 * The migrations, drawn by a Preact application.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\output;

/**
 * The page of the migrations: the AMD module local_filebank/migration_app reads from the element that this writes the migration
 * to open, the sizes of a page and the text, and asks the web services for everything else.
 */
class migration_page {
    /**
     * @var string The prefix of the language strings that the application reads.
     */
    private const PREFIX = 'mg_';

    /**
     * Start the application in the page and return what holds its place until it is drawn.
     *
     * @param int $planid The migration to open, or 0 for the list.
     * @return string The HTML.
     */
    public static function mount(int $planid): string {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_filebank/migration_app', 'init');
        $props = s(json_encode(self::props($planid), JSON_UNESCAPED_SLASHES));
        return self::placeholder()
            . '<div id="fbm-root" class="filebank-migration" data-region="fbm-app" data-props="' . $props . '"></div>';
    }

    /**
     * What the application starts from.
     *
     * @param int $planid The migration to open, or 0 for the list.
     * @return array The properties of the application.
     */
    public static function props(int $planid): array {
        return [
            'planid' => $planid,
            'pageurl' => (new \moodle_url('/local/filebank/admin/migration.php'))->out(false),
            'strings' => (object) self::strings(),
        ];
    }

    /**
     * Every text of the application, named the way it asks for it.
     *
     * @return string[] The texts by name, without the prefix of the language file.
     */
    public static function strings(): array {
        $strings = [];
        foreach (get_string_manager()->load_component_strings('local_filebank', current_language()) as $key => $value) {
            if (strpos($key, self::PREFIX) === 0) {
                $strings[substr($key, strlen(self::PREFIX))] = $value;
            }
        }
        return $strings;
    }

    /**
     * What holds the place of the migrations until the application is there: a few rows as grey blocks.
     *
     * @return string The HTML.
     */
    public static function placeholder(): string {
        $rows = str_repeat('<span class="fbm-skeleton-row"></span>', 5);
        return '<div class="filebank-migration fbm-boot" aria-hidden="true"><div class="fbm-skeleton">' . $rows . '</div></div>';
    }
}
