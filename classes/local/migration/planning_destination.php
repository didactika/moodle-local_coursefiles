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
 * Reviewed immutable family migration planning.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\local\catalog\material_source;
use local_filebank\local\profile\profiles;
use local_filebank\local\policy\author_policy;
use local_filebank\local\logging\log;

/**
 * Resolve reviewed placement overrides and record safe destination creation descriptors.
 */
final class planning_destination {
    /**
     * Resolve.
     *
     * @param \cm_info $sourcecm Sourcecm.
     * @param int $targetcourse Targetcourse.
     * @param array $config Config.
     * @param array $correspondence Correspondence.
     * @param array $captures Captures.
     * @param array $override Override.
     * @param array $creations Creations.
     * @return array Result.
     */
    public static function resolve(
        \cm_info $sourcecm,
        int $targetcourse,
        array $config,
        array $correspondence,
        array $captures,
        array $override,
        array &$creations
    ): array {

        global $DB;
        $sectionid = !empty($override['sectionid']) ? (int)$override['sectionid']
            : placement::section($sourcecm, $targetcourse, $config['section_match'] ?? []);
        $DB->get_record('course_sections', ['id' => $sectionid, 'course' => $targetcourse], '*', MUST_EXIST);
        $targets = self::targets($targetcourse, $sectionid, $correspondence, $captures, $override);

        self::require_supported($targets, $override, $config);
        if (!$targets) {
            $targetdata = target_binding::descriptor(
                $targetcourse,
                $sectionid,
                !empty($override['create']) ? ['field' => 'name', 'equals' => $sourcecm->name]
                    : $correspondence['target_module'],
                $captures
            );
            self::assert_unique($targetcourse, $sectionid, $targetdata);
            $targetkey = hash('sha256', json_encode($targetdata));
            $creations[$targetkey] = $targetdata;
            $target = (object)['id' => 0, 'context' => (object)['id' => 0]];
        } else {
            $target = $targets[0];
            $targetdata = placement::snapshot($target->id);
            $targetkey = '';
        }
        return [$target, $targetdata, $targetkey];
    }

    /**
     * Targets.
     *
     * @param int $targetcourse Destination course.
     * @param int $sectionid Reviewed destination section.
     * @param array $correspondence Module matching rule.
     * @param array $captures Source capture values.
     * @param array $override Reviewed placement override.
     * @return array Result.
     */
    private static function targets(
        int $targetcourse,
        int $sectionid,
        array $correspondence,
        array $captures,
        array $override
    ): array {
        $targets = [];
        foreach (get_fast_modinfo($targetcourse)->get_cms() as $targetcm) {
            if (
                (!empty($override['cmid']) ? $targetcm->id == $override['cmid']
                    : (!$override && $targetcm->section == $sectionid
                        && placement::match($targetcm, $correspondence['target_module'] ?? [], $captures) !== null))
            ) {
                $targets[] = $targetcm;
            }
        }
        return $targets;
    }

    /**
     * Assert unique.
     *
     * @param int $targetcourse Destination course.
     * @param int $sectionid Reviewed destination section.
     * @param array $targetdata Requested creation descriptor.
     */
    private static function assert_unique(int $targetcourse, int $sectionid, array $targetdata): void {
        foreach (get_fast_modinfo($targetcourse)->get_cms() as $existingcm) {
            if (
                $existingcm->modname === 'folder' && $existingcm->section == $sectionid
                && ($targetdata['idnumber'] !== '' ? $existingcm->idnumber === $targetdata['idnumber']
                    : $existingcm->name === $targetdata['name'])
            ) {
                throw new \moodle_exception('placementambiguous', 'local_filebank');
            }
        }
    }

    /**
     * Require supported.
     *
     * @param array $targets Targets.
     * @param array $override Override.
     * @param array $config Config.
     */
    private static function require_supported(array $targets, array $override, array $config): void {
        if (
            count($targets) > 1 || ($targets && $targets[0]->modname !== 'folder')
            || (!$targets && empty($override['create']) && ($config['missing_target'] ?? 'block') !== 'create')
        ) {
            throw new \moodle_exception('placementambiguous', 'local_filebank');
        }
    }
}
