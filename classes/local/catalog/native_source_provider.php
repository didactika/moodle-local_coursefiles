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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\catalog;

/**
 * Adapters for core teaching resources, public activity introductions and assignment handouts.
 */
final class native_source_provider implements source_provider {
    /** @var string Activity type. */
    private string $module;
    /** @var array Declared public areas. */
    private array $areas;

    /**
     * Bind one module and its verified public file areas.
     *
     * @param string $module Module name.
     * @param array $areas Area names.
     */
    public function __construct(string $module, array $areas) {
        $this->module = $module;
        $this->areas = $areas;
    }

    /**
     * Declare public file area names for this provider's module component.
     *
     * @return array File area names.
     */
    public function areas(): array {
        return $this->areas;
    }

    /**
     * Enumerate candidate files using the activity's supported APIs.
     *
     * @param \cm_info $cm Current source activity.
     * @return iterable Candidate stored files.
     */
    public function files(\cm_info $cm): iterable {
        foreach ($this->areas as $area) {
            foreach (
                get_file_storage()->get_area_files(
                    $cm->context->id,
                    'mod_' . $this->module,
                    $area,
                    $area === 'chapter' ? false : 0,
                    'id',
                    false
                ) as $file
            ) {
                if ($this->allows($cm, $file)) {
                    yield $file;
                }
            }
        }
    }

    /**
     * Check file ownership and activity-specific publication conditions.
     *
     * @param \cm_info $cm Current source activity.
     * @param \stored_file $file Candidate file.
     * @return bool Public teaching material.
     */
    public function allows(\cm_info $cm, \stored_file $file): bool {
        global $DB, $CFG;
        if (
            $file->get_contextid() != $cm->context->id || $file->get_component() !== 'mod_' . $cm->modname
                || $cm->modname !== $this->module || !in_array($file->get_filearea(), $this->areas, true)
                || $file->is_directory() || $file->is_external_file()
        ) {
            return false;
        }
        if ($this->module === 'book' && $file->get_filearea() === 'chapter') {
            return $DB->record_exists('book_chapters', [
                'id' => $file->get_itemid(), 'bookid' => $cm->instance, 'hidden' => 0,
            ]);
        }
        if ($file->get_itemid() != 0) {
            return false;
        }
        if ($this->module === 'assign') {
            // Moodle 5.3 serves introattachment independently of the timed activity instructions.
            if ($file->get_filearea() === 'introattachment' && $CFG->version >= 2026100500) {
                return true;
            }
            require_once($CFG->dirroot . '/mod/assign/locallib.php');
            return (new \assign($cm->context, $cm, get_course($cm->course)))->show_intro();
        }
        return true;
    }

    /**
     * Check ordinary source access without relying on a family publication grant.
     *
     * @param \cm_info $cm Current source activity.
     * @param \stored_file $file Public candidate file.
     * @return bool Current actor may read the source through its native access rules.
     */
    public function readable(\cm_info $cm, \stored_file $file): bool {
        if (!$cm->uservisible) {
            return false;
        }
        if ($file->get_filearea() === 'intro') {
            // Core file_pluginfile serves module introduction files to course users.
            return true;
        }
        $capability = $this->module === 'book' ? 'mod/book:read' : 'mod/' . $this->module . ':view';
        if (!has_capability($capability, $cm->context)) {
            return false;
        }
        if ($this->module === 'assign') {
            return $this->allows($cm, $file);
        }
        $info = get_file_browser()->get_file_info(
            $cm->context,
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        return $info && $info->is_readable();
    }
}
