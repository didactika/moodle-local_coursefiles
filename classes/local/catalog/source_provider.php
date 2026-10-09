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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\catalog;

/**
 * Explicit public teaching-area contract for community activity integrations.
 */
interface source_provider {
    /**
     * Declare public file area names for this provider's module component.
     *
     * @return array File area names.
     */
    public function areas(): array;

    /**
     * Enumerate candidate files using the activity's supported APIs.
     *
     * @param \cm_info $cm Current source activity.
     * @return iterable Candidate stored files.
     */
    public function files(\cm_info $cm): iterable;

    /**
     * Check file ownership and activity-specific publication conditions.
     *
     * @param \cm_info $cm Current source activity.
     * @param \stored_file $file Candidate file.
     * @return bool Public teaching material.
     */
    public function allows(\cm_info $cm, \stored_file $file): bool;

    /**
     * Check ordinary source access without relying on a family publication grant.
     *
     * @param \cm_info $cm Current source activity.
     * @param \stored_file $file Public candidate file.
     * @return bool Current actor may read the source through its native access rules.
     */
    public function readable(\cm_info $cm, \stored_file $file): bool;
}
