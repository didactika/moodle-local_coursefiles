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
 * Bounded operational history retention.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\task;

/**
 * Scheduled retention of operational records.
 */
final class clean_history extends \core\task\scheduled_task {
    /**
     * Return the scheduled task label.
     *
     * @return string Task name.
     */
    public function get_name(): string {
        return get_string('cleanhistory', 'local_multipicker');
    }

    /**
     * Run one bounded cleanup batch.
     */
    public function execute(): void {
        $counts = \local_multipicker\local\maintenance\retention::clean();
        mtrace('MultiPicker: removed ' . $counts['logs'] . ' logs and ' . $counts['plans']
            . ' expired plans and ' . $counts['versions'] . ' profile versions.');
    }
}
