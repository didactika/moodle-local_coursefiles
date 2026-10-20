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
 * The migrations, drawn by a React component.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\output;

use core\output\react_component_renderable;

/**
 * The page of the migrations: the component local_filebank/Migration gets here the migration to open, the sizes of a page and the
 * text, and asks the web services for everything else.
 */
class migration_page implements react_component_renderable, \renderable {
    /**
     * @var string The prefix of the language strings that the component reads.
     */
    private const PREFIX = 'mg_';

    /**
     * @var int The migration to open, or 0 for the list.
     */
    private int $planid;

    /**
     * Keep what the page was asked for.
     *
     * @param int $planid The migration to open, or 0 for the list.
     */
    public function __construct(int $planid) {
        $this->planid = $planid;
    }

    /**
     * The React component that draws the migrations.
     *
     * @return string Name of the component inside the plugin.
     */
    #[\Override]
    public function get_react_component_name(): string {
        return 'local_filebank/Migration';
    }

    /**
     * What the component starts from.
     *
     * @param \renderer_base $renderer Renderer.
     * @return \stdClass Properties of the component.
     */
    #[\Override]
    public function get_react_component_props(\renderer_base $renderer): \stdClass {
        unset($renderer);
        return (object) [
            'planid' => $this->planid,
            'pageurl' => (new \moodle_url('/local/filebank/admin/migration.php'))->out(false),
            'pagesizes' => [10, 25, 50],
            'strings' => (object) self::strings(),
        ];
    }

    /**
     * Every text of the component, named the way the component asks for it.
     *
     * @return string[] The texts by name, without the prefix of the language file.
     */
    public static function strings(): array {
        $strings = [];
        foreach (get_string_manager()->load_component_strings('local_filebank', current_language()) as $key => $value) {
            if (str_starts_with($key, self::PREFIX)) {
                $strings[substr($key, strlen(self::PREFIX))] = $value;
            }
        }
        return $strings;
    }

    /**
     * What holds the place of the migrations until their component is there: a head and a few rows as grey blocks.
     *
     * @return string The HTML.
     */
    public static function placeholder(): string {
        $rows = str_repeat('<span class="fbm-skeleton-row"></span>', 5);
        return '<div class="filebank-migration fbm-boot" aria-hidden="true"><div class="fbm-skeleton">' . $rows . '</div></div>';
    }
}
