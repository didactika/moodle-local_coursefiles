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
 * Theme-overridable CourseFiles renderer.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefiles\output;

/**
 * Delegates presentation to component templates and the active Moodle theme.
 */
class renderer extends \plugin_renderer_base {
    /**
     * Render a prepared view.
     *
     * @param view $view Presentation context.
     * @return string HTML.
     */
    public function render_view(view $view): string {
        return $this->render_from_template($view->template(), $view->export_for_template($this));
    }
}
