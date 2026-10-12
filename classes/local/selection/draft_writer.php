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
 * Reviewed batches for existing native form drafts.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\local\catalog\material_source;
use local_filebank\local\migration\copy_decision;
use local_filebank\local\policy\author_policy;
use local_filebank\local\profile\profiles;

/**
 * Reauthorize and write a reviewed source leaf with its captured attribution inside the caller transaction.
 */
final class draft_writer {
    /**
     * Copy.
     *
     * @param int $courseid Courseid.
     * @param int $profileid Profileid.
     * @param int $draftid Draftid.
     * @param \context_user $context Context.
     * @param array $operation Operation.
     */
    public static function copy(
        int $courseid,
        int $profileid,
        int $draftid,
        \context_user $context,
        array $operation
    ): void {
        global $DB, $USER;
        [$file, $cm] = material_source::resolve($operation['pathnamehash'], $courseid, $profileid);
        author_policy::require_native_copy($file, (int)$cm->course, $courseid);
        if (copy_decision::snapshot($file) !== $operation['snapshot']) {
            throw new \moodle_exception('batchconflict', 'local_filebank');
        }
        $attributionid = author_policy::capture($file);
        $uuid = $DB->get_field('local_filebank_author', 'uuid', ['id' => $attributionid], MUST_EXIST);
        get_file_storage()->create_file_from_storedfile(
            [
                'contextid' => $context->id, 'component' => 'user', 'filearea' => 'draft', 'itemid' => $draftid,
                'filepath' => $operation['filepath'], 'filename' => $operation['filename'], 'userid' => $USER->id,
                'author' => $file->get_author(), 'license' => $file->get_license(),
                // A source activity's main-file choice must not replace the destination's choice.
                'sortorder' => 0,
                'source' => serialize((object)['source' => json_encode(['filebank' => $uuid])]),
            ],
            $file
        );
    }
}
