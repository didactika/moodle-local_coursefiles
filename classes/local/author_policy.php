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
 * Site/course author protection and provenance.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\local;

/**
 * Resolves effective protection independently of content identity.
 */
final class author_policy {
    /**
     * Resolve a course lock.
     *
     * @param int $courseid Course.
     * @return bool Required lock.
     */
    public static function locked(int $courseid): bool {
        global $DB;
        if (get_config('local_coursebridge', 'author_policy') !== 'editable_with_provenance') {
            return true;
        }
        return get_config('local_coursebridge', 'author_course_override') === 'tighten_only'
            && $DB->get_field('local_coursebridge_coursepolicy', 'policy', ['courseid' => $courseid]) === 'preserve_locked';
    }

    /**
     * Require a supported copy workflow.
     *
     * @param \stored_file $file Source.
     * @param int $sourcecourse Source course.
     * @param int $targetcourse Target course.
     */
    public static function require_native_copy(\stored_file $file, int $sourcecourse, int $targetcourse): void {
        global $DB;
        $origin = $DB->get_record('local_coursebridge_origin', ['pathnamehash' => $file->get_pathnamehash()]);
        $originlocked = $origin && $DB->get_field(
            'local_coursebridge_attribution',
            'locked',
            ['id' => $origin->attributionid]
        );
        if (self::locked($sourcecourse) || self::locked($targetcourse) || $originlocked) {
            throw new \moodle_exception('lockedunsupported', 'local_coursebridge');
        }
        if (!$file->get_author() && get_config('local_coursebridge', 'author_missing_policy') === 'block_reuse') {
            throw new \moodle_exception('missingauthor', 'local_coursebridge');
        }
    }

    /**
     * Save a tighten-only course setting.
     *
     * @param int $courseid Course.
     * @param string $policy Choice.
     */
    public static function save(int $courseid, string $policy): void {
        global $DB, $USER;
        require_capability('local/coursebridge:managecoursepolicy', \context_course::instance($courseid));
        if (
            get_config('local_coursebridge', 'author_course_override') !== 'tighten_only'
                || !in_array($policy, ['inherit', 'preserve_locked'], true)
        ) {
            throw new \moodle_exception('invalidpolicy', 'local_coursebridge');
        }
        $record = $DB->get_record('local_coursebridge_coursepolicy', ['courseid' => $courseid]);
        $data = (object)['courseid' => $courseid, 'policy' => $policy,
            'revision' => ($record->revision ?? 0) + 1, 'userid' => $USER->id, 'timemodified' => time()];
        if ($record) {
            $data->id = $record->id;
            $DB->update_record('local_coursebridge_coursepolicy', $data);
        } else {
            $DB->insert_record('local_coursebridge_coursepolicy', $data);
        }
        log::record('course_policy_changed', '', ['courseid' => $courseid, 'policy' => $policy]);
    }

    /**
     * Capture the chosen source declaration.
     *
     * @param \stored_file $file Source.
     * @return int Attribution.
     */
    public static function capture(\stored_file $file): int {
        global $DB, $USER;
        $parent = $DB->get_record('local_coursebridge_origin', ['pathnamehash' => $file->get_pathnamehash()]);
        $parentuuid = $parent ? $DB->get_field(
            'local_coursebridge_attribution',
            'uuid',
            ['id' => $parent->attributionid]
        ) : '';
        return $DB->insert_record('local_coursebridge_attribution', (object)[
            'uuid' => \core\uuid::generate(), 'parentuuid' => $parentuuid ?: '',
            'sourcehash' => $file->get_pathnamehash(), 'contenthash' => $file->get_contenthash(),
            'author' => $file->get_author(), 'license' => $file->get_license(), 'locked' => 0,
            'userid' => $USER->id, 'timecreated' => time(),
        ]);
    }
}
