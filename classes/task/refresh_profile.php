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
 * Bounded course reconciliation after profile activation.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\task;

use local_multipicker\local\catalog\indexer;
use local_multipicker\local\profile\profiles;

/**
 * Refreshes matching or previously indexed courses without a whole-site form request.
 */
final class refresh_profile extends \core\task\adhoc_task {
    /** @var int Maximum courses examined in one task slice. */
    private const BATCH_SIZE = 25;

    /**
     * Queue a deduplicated revision-bound slice through Moodle cron.
     *
     * @param int $profileid Profile.
     * @param int $revisionid Activated revision.
     * @param int $after Last examined course ID.
     */
    public static function queue(int $profileid, int $revisionid, int $after = 0): void {
        $task = new self();
        $task->set_custom_data(['profileid' => $profileid, 'revisionid' => $revisionid, 'after' => $after]);
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Refresh a bounded slice and queue its successor, ignoring obsolete revisions.
     */
    public function execute(): void {
        global $DB;
        $data = $this->get_custom_data();
        $profileid = (int)$data->profileid;
        if (
            !$DB->record_exists('local_multipicker', ['id' => $profileid, 'activeid' => (int)$data->revisionid,
                'enabled' => 1, 'deleted' => 0])
        ) {
            return;
        }
        [, , $rules] = profiles::load($profileid);
        $courses = $DB->get_records_select(
            'course',
            'id > ? AND id <> ?',
            [(int)$data->after, SITEID],
            'id',
            'id, idnumber, shortname, fullname, category',
            0,
            self::BATCH_SIZE
        );
        $after = (int)$data->after;
        foreach ($courses as $course) {
            $previous = $DB->record_exists('local_multipicker_course', ['courseid' => $course->id, 'profileid' => $profileid]);
            try {
                $identity = $rules->resolve($course);
            } catch (\moodle_exception $exception) {
                $identity = null;
            }
            if ($previous || $identity) {
                indexer::refresh((int)$course->id);
            }
            $after = (int)$course->id;
        }
        if (count($courses) === self::BATCH_SIZE) {
            self::queue($profileid, (int)$data->revisionid, $after);
        }
    }
}
