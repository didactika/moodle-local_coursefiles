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
 * Native browser fixture generator mapping.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Makes published multi-edition materials available to native browser scenarios.
 */
final class behat_local_coursebridge_generator extends behat_generator_base {
    /**
     * Describe component fixtures available to Moodle's generic generator step.
     *
     * @return array Entities.
     */
    protected function get_creatable_entities(): array {
        return ['folder fixtures' => [
            'singular' => 'folder fixture', 'datagenerator' => 'folder_fixture', 'required' => ['name'],
        ], 'migration fixtures' => [
            'singular' => 'migration fixture', 'datagenerator' => 'migration_fixture', 'required' => ['name'],
        ]];
    }
}
