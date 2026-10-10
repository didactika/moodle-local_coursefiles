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
 * Incremental reconstructible material catalog.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\catalog;

use local_filebank\local\profile\profiles;

/**
 * Refreshes one course at a time under a shared metadata lock.
 */
final class indexer {
    /**
     * Rebuild course membership and supported file locations.
     *
     * @param int $courseid Course.
     */
    public static function refresh(int $courseid): void {
        global $DB;
        $lock = \core\lock\lock_config::get_lock_factory('local_filebank')->get_lock('index:' . $courseid, 10);
        if (!$lock) {
            throw new \moodle_exception('lockfailed', 'local_filebank');
        }
        try {
            $course = $DB->get_record('course', ['id' => $courseid]);
            $transaction = $DB->start_delegated_transaction();
            $DB->delete_records('local_filebank_value', ['courseid' => $courseid]);
            $DB->delete_records('local_filebank_course', ['courseid' => $courseid]);
            $DB->delete_records('local_filebank_file', ['courseid' => $courseid]);
            if ($course && $courseid != SITEID) {
                foreach ($DB->get_records('local_filebank', ['enabled' => 1]) as $profile) {
                    [$profile, $revision, $rules] = profiles::load($profile->id);
                    try {
                        $identity = $rules->resolve($course);
                    } catch (\moodle_exception $exception) {
                        $identity = null;
                    }
                    if (!$identity) {
                        continue;
                    }
                    $DB->insert_record('local_filebank_course', (object)[
                        'profileid' => $profile->id, 'revisionid' => $revision->id, 'courseid' => $courseid,
                        'familykey' => $identity['familykey'], 'familyhash' => $identity['familyhash'],
                        'edition' => $identity['edition'], 'dimensions' => json_encode($identity['dimensions']),
                        'timemodified' => time(),
                    ]);
                    foreach ($identity['filters'] as $name => $value) {
                        if ($value !== '') {
                            $DB->insert_record('local_filebank_value', (object)[
                                'profileid' => $profile->id, 'revisionid' => $revision->id,
                                'courseid' => $courseid, 'name' => $name, 'value' => $value,
                            ]);
                        }
                    }
                }
                foreach (get_fast_modinfo($courseid)->get_cms() as $cm) {
                    if (
                        !source_registry::provider($cm->modname)
                            || !$course->visible || !$cm->visible || !empty($cm->availability)
                    ) {
                        continue;
                    }
                    $files = source_registry::provider($cm->modname)->files($cm);
                    foreach ($files as $file) {
                        if (!$file->is_external_file()) {
                            $DB->insert_record('local_filebank_file', (object)[
                                'courseid' => $courseid, 'cmid' => $cm->id, 'pathnamehash' => $file->get_pathnamehash(),
                                'contenthash' => $file->get_contenthash(), 'filesize' => $file->get_filesize(),
                                'filename' => $file->get_filename(), 'mimetype' => $file->get_mimetype(),
                                'timemodified' => time(),
                            ]);
                        }
                    }
                }
            }
            $transaction->allow_commit();
        } finally {
            $lock->release();
        }
    }
}
