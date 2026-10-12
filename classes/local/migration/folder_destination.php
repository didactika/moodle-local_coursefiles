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
 * Additive native folder file lifecycle adapter.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\policy\author_policy;

/**
 * Applies additive file copies without replacing an unrelated file or rebuilding the file area.
 */
final class folder_destination {
    /**
     * Apply one reviewed additive file operation.
     *
     * @param \stored_file $source Authorized source.
     * @param int $cmid Destination folder.
     * @param bool $overwrite Explicitly reviewed replacement.
     * @param array|null $expected Reviewed destination snapshot, or null for a new path.
     * @return \stored_file Saved file.
     */
    public static function copy(\stored_file $source, int $cmid, bool $overwrite, ?array $expected = null): \stored_file {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/mod/folder/lib.php');
        $cm = get_coursemodule_from_id('folder', $cmid, 0, false, MUST_EXIST);
        require_capability('moodle/course:manageactivities', \context_module::instance($cmid));
        $sourcecontext = \context::instance_by_id($source->get_contextid())->get_course_context(false);
        if (!$sourcecontext) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        author_policy::require_native_copy($source, (int)$sourcecontext->instanceid, (int)$cm->course);
        $context = \context_module::instance($cmid);
        $fs = get_file_storage();
        $transaction = $DB->start_delegated_transaction();
        $existing = $fs->get_file(
            $context->id,
            'mod_folder',
            'content',
            0,
            $source->get_filepath(),
            $source->get_filename()
        );
        if (($existing ? copy_decision::snapshot($existing) : null) !== $expected) {
            throw new \moodle_exception('planstale', 'local_filebank');
        }
        if ($existing) {
            if (!$overwrite) {
                throw new \moodle_exception('planstale', 'local_filebank');
            }
            author_policy::capture($existing);
            $existing->delete();
        }
        $record = ['contextid' => $context->id, 'component' => 'mod_folder', 'filearea' => 'content',
            'itemid' => 0, 'filepath' => $source->get_filepath(), 'filename' => $source->get_filename(),
            'author' => $source->get_author(), 'license' => $source->get_license(), 'userid' => $USER->id];
        $fs->create_file_from_storedfile($record, $source);
        $folder = $DB->get_record('folder', ['id' => $cm->instance], '*', MUST_EXIST);
        $folder->timemodified = time();
        $DB->update_record('folder', $folder);
        $event = \mod_folder\event\folder_updated::create(['context' => $context, 'objectid' => $folder->id]);
        $event->add_record_snapshot('folder', $folder);
        $event->trigger();
        $transaction->allow_commit();
        rebuild_course_cache($cm->course, true);
        $saved = $fs->get_file(
            $context->id,
            'mod_folder',
            'content',
            0,
            $source->get_filepath(),
            $source->get_filename()
        );
        if (
            !$saved || copy_decision::classify(
                copy_decision::snapshot($source),
                copy_decision::snapshot($saved)
            ) !== 'skip_identical'
        ) {
            throw new \moodle_exception('verificationfailed', 'local_filebank');
        }
        return $saved;
    }
}
