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
 * Theme-overridable native presentation context.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\output;

/**
 * Carries plain data to a named component template.
 */
final class view implements \renderable, \templatable {
    /** @var array Named views and their organized component templates. */
    private const TEMPLATES = [
        'navigation' => 'local/navigation/tabs',
        'migration_setup' => 'local/migration/setup',
        'profile_summary' => 'local/profile/summary',
        'catalog' => 'local/catalog/index',
        'migration_report' => 'local/migration/report',
        'policy' => 'local/policy/course',
        'profile_list' => 'local/profile/list',
        'log' => 'local/log/index',
        'selection_review' => 'local/selection/review',
    ];
    /** @var string Approved template name. */
    private string $template;
    /** @var array Presentation data. */
    private array $data;

    /**
     * Prepare a component view without introducing HTML into service results.
     *
     * @param string $template Component template basename.
     * @param array $data Plain presentation context.
     */
    public function __construct(string $template, array $data) {
        if (!isset(self::TEMPLATES[$template])) {
            throw new \coding_exception('Unknown MultiPicker view');
        }
        $this->template = self::TEMPLATES[$template];
        $this->data = $data;
    }

    /**
     * Return the stable theme override identifier.
     *
     * @return string Component template.
     */
    public function template(): string {
        return 'local_multipicker/' . $this->template;
    }

    /**
     * Export the prepared context.
     *
     * @param \renderer_base $output Active renderer.
     * @return array Plain data.
     */
    public function export_for_template(\renderer_base $output): array {
        if ($this->template === 'local/migration/report' && !isset($this->data['courses'])) {
            $data = $this->data;
            $data['courses'] = array_map(static fn($group) => $group + ['folders' => [$group]], $data['groups'] ?? []);
            return $data;
        }
        if ($this->template === 'local/navigation/tabs') {
            $root = \navigation_node::create(get_string('pluginname', 'local_multipicker'));
            foreach ($this->data['tabs'] as $index => $tab) {
                $node = $root->add($tab['label'], new \moodle_url($tab['url']), null, null, 'multipicker-' . $index);
                if ($tab['active']) {
                    $node->make_active();
                }
            }
            $menu = new \core\navigation\output\more_menu($root, 'nav-tabs');
            return $this->data + ['menu' => $menu->export_for_template($output)];
        }
        return $this->data;
    }
}
