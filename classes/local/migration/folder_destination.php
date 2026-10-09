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
 * Maintenance-window native folder lifecycle adapter.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

use local_multipicker\local\policy\author_policy;

/**
 * Applies additive file copies using the folder module's own save contract.
 */
final class folder_destination {
    /**
     * Apply one reviewed additive file operation.
     *
     * @param \stored_file $source Authorized source.
     * @param int $cmid Destination folder.
     * @return \stored_file Saved file.
     */
    public static function copy(\stored_file $source, int $cmid): \stored_file {
        global $CFG, $DB, $USER;
        if (empty($CFG->maintenance_enabled)) {
            throw new \moodle_exception('maintenance_required', 'local_multipicker');
        }
        require_once($CFG->dirroot . '/mod/folder/lib.php');
        $cm = get_coursemodule_from_id('folder', $cmid, 0, false, MUST_EXIST);
        require_capability('moodle/course:manageactivities', \context_module::instance($cmid));
        $sourcecontext = \context::instance_by_id($source->get_contextid())->get_course_context(false);
        if (!$sourcecontext) {
            throw new \moodle_exception('materialunavailable', 'local_multipicker');
        }
        author_policy::require_native_copy($source, (int)$sourcecontext->instanceid, (int)$cm->course);
        $context = \context_module::instance($cmid);
        $fs = get_file_storage();
        $draftid = 0;
        file_prepare_draft_area($draftid, $context->id, 'mod_folder', 'content', 0, ['subdirs' => true]);
        $record = ['contextid' => \context_user::instance($USER->id)->id, 'component' => 'user',
            'filearea' => 'draft', 'itemid' => $draftid, 'filepath' => $source->get_filepath(),
            'filename' => $source->get_filename(), 'author' => $source->get_author(),
            'license' => $source->get_license(), 'userid' => $USER->id];
        $fs->create_file_from_storedfile($record, $source);
        $folder = $DB->get_record('folder', ['id' => $cm->instance], '*', MUST_EXIST);
        $folder->instance = $folder->id;
        $folder->coursemodule = $cmid;
        $folder->files = $draftid;
        $folder->completionexpected = $DB->get_field('event', 'timestart', [
            'modulename' => 'folder', 'instance' => $folder->id,
            'eventtype' => \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED,
        ]) ?: null;
        $oldpost = $_POST;
        $oldget = $_GET;
        $oldrequest = $_REQUEST;
        try {
            $_POST['files'] = $draftid;
            $_POST['sesskey'] = sesskey();
            $_REQUEST['files'] = $draftid;
            unset($_GET['files']);
            folder_update_instance($folder, null);
            $event = \mod_folder\event\folder_updated::create([
                'context' => $context, 'objectid' => $folder->id,
            ]);
            $event->add_record_snapshot('folder', $folder);
            $event->trigger();
            rebuild_course_cache($cm->course, true);
        } finally {
            $_POST = $oldpost;
            $_GET = $oldget;
            $_REQUEST = $oldrequest;
            $fs->delete_area_files(\context_user::instance($USER->id)->id, 'user', 'draft', $draftid);
        }
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
            throw new \moodle_exception('verificationfailed', 'local_multipicker');
        }
        return $saved;
    }
}
