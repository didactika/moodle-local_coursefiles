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
 * Authorized names for persistent mixed selections.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\selection;

use local_filebank\local\catalog\material_source;
use local_filebank\output\display;

/**

 * Shows removable selected items without trusting or disclosing stale source metadata.

 */
final class selection_basket {
    /**
     * Describe a bounded selection, rechecking publication and permissions before displaying names.
     *
     * @param int $courseid Destination.
     * @param int $profileid Current profile.
     * @param array $values Selected native tokens.
     * @return array Safe display rows; unavailable selections remain removable.
     */
    public static function data(int $courseid, int $profileid, array $values): array {
        global $DB;
        $items = selection_options::parse($values);
        $rows = [];
        foreach ($items as $item) {
            $value = isset($item['pathnamehash']) ? 'file:' . $item['pathnamehash']
                : 'folder:' . $item['cmid'] . ':' . base64_encode($item['filepath']);
            $title = get_string('selectionunavailable', 'local_filebank');
            try {
                $hash = $item['pathnamehash'] ?? '';
                if (!$hash) {
                    $context = \context_module::instance($item['cmid']);
                    $records = $DB->get_records_select(
                        'files',
                        'contextid = :contextid AND component = :component'
                        . ' AND filearea = :area AND itemid = 0 AND filename <> :directory AND '
                        . $DB->sql_like('filepath', ':path', true),
                        ['contextid' => $context->id, 'component' => 'mod_folder', 'area' => 'content',
                            'directory' => '.', 'path' => $DB->sql_like_escape($item['filepath']) . '%'],
                        'id',
                        'id, pathnamehash',
                        0,
                        1
                    );
                    $hash = $records ? reset($records)->pathnamehash : '';
                }
                [$file, $cm, , $course] = material_source::resolve($hash, $courseid, $profileid);
                $coursecontext = \context_course::instance($course->id);
                $title = display::name($course->shortname, $coursecontext) . ' / '
                    . display::name($cm->name, \context_module::instance($cm->id)) . ' / '
                    . (isset($item['filepath']) ? s($item['filepath']) : s($file->get_filename()));
            } catch (\moodle_exception $exception) {
                $title = get_string('selectionunavailable', 'local_filebank');
            }
            $rows[] = ['value' => $value, 'title' => $title];
        }
        return $rows;
    }
}
