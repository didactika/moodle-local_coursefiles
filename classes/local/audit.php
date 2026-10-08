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
 * Structured actor and outcome audit.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local;

/**
 * Records actions without credentials or binaries.
 */
final class audit {
    /**
     * Append an action.
     *
     * @param string $action Action.
     * @param string $uuid Entity.
     * @param array $details Outcome.
     */
    public static function record(string $action, string $uuid, array $details): void {
        global $DB, $USER;
        $DB->insert_record('local_coursebridge_audit', (object)[
            'userid' => (int)($USER->id ?? 0), 'action' => $action, 'entityuuid' => $uuid,
            'details' => json_encode($details), 'timecreated' => time(),
        ]);
    }
}
