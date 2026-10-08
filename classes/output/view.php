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
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\output;

/**
 * Carries plain data to a named component template.
 */
final class view implements \renderable, \templatable {
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
        if (!in_array($template, ['catalog', 'migration_report', 'policy', 'profile_list'], true)) {
            throw new \coding_exception('Unknown CourseBridge view');
        }
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Return the stable theme override identifier.
     *
     * @return string Component template.
     */
    public function template(): string {
        return 'local_coursebridge/' . $this->template;
    }

    /**
     * Export the prepared context.
     *
     * @param \renderer_base $output Active renderer.
     * @return array Plain data.
     */
    public function export_for_template(\renderer_base $output): array {
        return $this->data;
    }
}
