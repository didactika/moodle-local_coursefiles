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
 * Native owning-field selection capacity.
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\selection;

/**
 * Enforces server-owned file count limits before persisting a selection.
 */
final class selection_limits {
    /**
     * Remaining selection slots, accounting for existing draft files.
     * @param array $scope Validated native field binding.
     * @return int Available slots.
     */
    public static function capacity(array $scope): int {
        global $USER;
        $limit = min(material_selection::MAX_FILES, $scope['limits']['selectionlimit'] ?? material_selection::MAX_FILES);
        $max = $scope['limits']['maxfiles'];
        if ($max >= 0) {
            $current = get_file_storage()->get_area_files(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $scope['draftid'],
                'id',
                false
            );
            $limit = min($limit, max(0, $max - count($current)));
        }
        return $limit;
    }

    /**
     * Refuse an oversized canonical selection without changing session state.
     * @param array $scope Validated scope.
     * @param array $selected Recursive file occurrences.
     */
    public static function require_capacity(array $scope, array $selected): void {
        if (count(array_unique($selected)) > self::capacity($scope)) {
            throw new \moodle_exception('selectionfull', 'local_multipicker');
        }
    }
}
