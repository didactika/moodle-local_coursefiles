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
 * Bounded accountable migration task.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\task;

/**
 * Requeues only bounded pending work under the original acting user.
 */
final class execute_migration extends \core\task\adhoc_task {
    /**
     * Execute one slice.
     */
    public function execute(): void {
        global $DB;
        if (!$DB->record_exists('local_filebank_run', ['id' => $this->get_custom_data()->runid])) {
            return;
        }
        $runid = (int)$this->get_custom_data()->runid;
        if (\local_filebank\local\migration\migration_executor::execute($runid)) {
            $next = new self();
            $next->set_userid($this->get_userid());
            $next->set_custom_data(['runid' => $runid]);
            \core\task\manager::queue_adhoc_task($next);
        }
    }
}
