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
 * What a migration leaves out without anything being wrong.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\migration;

/**
 * Counts the things that a migration does not move because they do not match, such as a course of the source that has no course
 * in the destination or a folder that the source does not have. None of them is an error and none asks for a decision, so they are
 * only counted and shown as information.
 */
final class migration_notes {
    /**
     * @var string A pair of course families whose destination has no course for the filters that were asked for.
     */
    public const NO_DESTINATION_COURSE = 'nodestinationcourse';

    /**
     * @var string A course of the source that has no folder that the correspondences describe.
     */
    public const NO_SOURCE_FOLDER = 'nosourcefolder';

    /**
     * @var string A folder of the source whose destination has no such folder and was asked not to create it.
     */
    public const NO_DESTINATION_FOLDER = 'nodestinationfolder';

    /**
     * @var string A folder of the source that is hidden or restricted, so it is not public material.
     */
    public const NOT_PUBLIC = 'notpublic';

    /**
     * @var int[] How many times each kind of thing was left out.
     */
    private array $counts = [];

    /**
     * Count one thing that was left out.
     *
     * @param string $kind One of the constants of this class.
     */
    public function add(string $kind): void {
        $this->counts[$kind] = ($this->counts[$kind] ?? 0) + 1;
    }

    /**
     * What was left out, by kind.
     *
     * @return int[] The counts, without the kinds that have none.
     */
    public function counts(): array {
        return $this->counts;
    }
}
