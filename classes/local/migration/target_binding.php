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
 * Reviewed immutable family migration planning.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\migration;

use local_multipicker\local\profile\profile_config;

/**
 * Creates a destination once, from an immutable reviewed definition.
 */
final class target_binding {
    /**
     * Describe an unambiguous folder creation without changing a course.
     *
     * @param int $courseid Target course.
     * @param int $sectionid Existing target section.
     * @param array $predicate Target name/idnumber rule.
     * @param array $captures Matched source values.
     * @return array Expected placement.
     */
    public static function descriptor(int $courseid, int $sectionid, array $predicate, array $captures): array {
        global $DB;
        $field = $predicate['field'] ?? 'name';
        $value = $predicate['equals'] ?? (isset($predicate['template'])
            ? profile_config::template($predicate['template'], $captures) : null);
        if (!is_string($value) || $value === '' || \core_text::strlen($value) > 255) {
            throw new \moodle_exception('creationnamerequired', 'local_multipicker');
        }
        $section = $DB->get_record('course_sections', ['id' => $sectionid, 'course' => $courseid], '*', MUST_EXIST);
        return ['cmid' => 0, 'courseid' => $courseid, 'sectionid' => $sectionid, 'sectionname' => $section->name,
            'sectionnum' => (int)$section->section, 'name' => $field === 'name' ? $value : $captures['name'],
            'idnumber' => $field === 'idnumber' ? $value : '', 'visible' => 1, 'availability' => null,
            'sectionvisible' => (int)$section->visible, 'sectionavailability' => $section->availability,
            'authorlocked' => \local_multipicker\local\policy\author_policy::locked($courseid)];
    }

    /**
     * Bind a native destination while holding the caller's destination lock.
     *
     * @param int $planid Reviewed plan.
     * @param string $key Immutable creation key.
     * @param array $definition Expected placement stored in the operation.
     * @return array Native placement snapshot.
     */
    public static function resolve(int $planid, string $key, array $definition): array {
        global $CFG, $DB;
        if (empty($CFG->maintenance_enabled) || !hash_equals($key, hash('sha256', json_encode($definition)))) {
            throw new \moodle_exception('planstale', 'local_multipicker');
        }
        $binding = $DB->get_record('local_multipicker_target', ['planid' => $planid, 'targetkey' => $key], '*', MUST_EXIST);
        if (json_decode($binding->definitionjson, true) !== $definition) {
            throw new \moodle_exception('planstale', 'local_multipicker');
        }
        require_capability('moodle/course:manageactivities', \context_course::instance($definition['courseid']));
        if ($binding->cmid) {
            $actual = placement::snapshot((int)$binding->cmid);
            if ($actual !== json_decode($binding->snapshotjson, true)) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
            return $actual;
        }
        require_capability('mod/folder:addinstance', \context_course::instance($definition['courseid']));
        $section = $DB->get_record('course_sections', ['id' => $definition['sectionid'],
            'course' => $definition['courseid']], '*', MUST_EXIST);
        if (
            $section->name !== $definition['sectionname'] || (int)$section->section !== $definition['sectionnum']
            || (int)$section->visible !== $definition['sectionvisible']
            || $section->availability !== $definition['sectionavailability']
                || \local_multipicker\local\policy\author_policy::locked($definition['courseid']) !== $definition['authorlocked']
        ) {
            throw new \moodle_exception('planstale', 'local_multipicker');
        }
        foreach (get_fast_modinfo($definition['courseid'])->get_cms() as $cm) {
            if (
                $cm->modname === 'folder' && (int)$cm->section === $definition['sectionid']
                && ($definition['idnumber'] !== '' ? $cm->idnumber === $definition['idnumber']
                    : $cm->name === $definition['name'])
            ) {
                throw new \moodle_exception('planstale', 'local_multipicker');
            }
        }
        require_once($CFG->dirroot . '/course/modlib.php');
        $transaction = $DB->start_delegated_transaction();
        $module = (object)['modulename' => 'folder', 'module' => $DB->get_field('modules', 'id', ['name' => 'folder'], MUST_EXIST),
            'course' => $definition['courseid'], 'section' => $definition['sectionnum'],
            'name' => $definition['name'], 'cmidnumber' => $definition['idnumber'], 'visible' => 1,
            'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0],
            'files' => 0, 'display' => 0, 'showexpanded' => 1, 'showdownloadfolder' => 1, 'forcedownload' => 1];
        $module = add_moduleinfo($module, get_course($definition['courseid']));
        $binding->cmid = $module->coursemodule;
        $actual = placement::snapshot((int)$binding->cmid);
        $binding->snapshotjson = json_encode($actual);
        $DB->update_record('local_multipicker_target', $binding);
        $transaction->allow_commit();
        return $actual;
    }
}
