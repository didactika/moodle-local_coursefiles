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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\selection;

/**
 * Load the native field extension only on supported owning controller routes.
 */
final class native_integration {
    /**
     * Keep unrelated requests and form factories on their normal core implementation.
     *
     * @param \core\hook\after_config $hook Bootstrap hook.
     */
    public static function register(\core\hook\after_config $hook): void {
        global $SCRIPT;
        if (in_array($SCRIPT, ['/mod/folder/edit.php', '/course/modedit.php'], true)) {
            \local_multipicker\form\native_filemanager::register_native($hook);
        }
    }
}
