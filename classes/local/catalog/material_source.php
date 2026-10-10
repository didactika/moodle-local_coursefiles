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
 * Permission-aware public activity material resolver.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\catalog;

use local_filebank\local\profile\profiles;
use local_filebank\local\logging\log;

/**
 * Resolves only declared teaching file areas and current permission.
 */
final class material_source {
    /**
     * Resolve and authorize a concrete file.
     *
     * @param string $pathnamehash Locator.
     * @param int $targetcourse Native destination course.
     * @param int $profileid Relationship profile.
     * @param array|null $profiledata Request-scoped loaded immutable profile.
     * @param array|null $scope Request-scoped current module/identity cache.
     * @return array File and current source module.
     */
    public static function resolve(
        string $pathnamehash,
        int $targetcourse,
        int $profileid,
        ?array $profiledata = null,
        ?array &$scope = null
    ): array {
        global $DB, $USER;
        \local_filebank\local\selection\destination_access::require($targetcourse);
        if (!preg_match('/^[a-f0-9]{40}$/', $pathnamehash)) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        $file = get_file_storage()->get_file_by_hash($pathnamehash);
        if (
            !$file || $file->is_directory() || $file->is_external_file()
        ) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        $context = \context::instance_by_id($file->get_contextid());
        if ($context->contextlevel !== CONTEXT_MODULE) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        [$profile, $revision, $rules] = $profiledata ?? profiles::load($profileid);
        if (!isset($scope[$context->instanceid])) {
            $cm = get_coursemodule_from_id(null, $context->instanceid, 0, false, MUST_EXIST);
            $course = get_course($cm->course);
            $section = $DB->get_record('course_sections', ['id' => $cm->section], '*', MUST_EXIST);
            $scope[$context->instanceid] = [$cm, $course, $section, $rules->resolve($course),
                $rules->resolve(get_course($targetcourse))];
        }
        [$cm, $course, $section, $sourceidentity, $targetidentity] = $scope[$context->instanceid];
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);
        $provider = source_registry::provider($cm->modname);
        if (!$provider || !$provider->allows($cminfo, $file)) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        $allowedareas = $rules->get_config()['publication']['allowed_sources'] ?? ['mod_folder/content', 'mod_resource/content'];
        if (
            !$sourceidentity || !$targetidentity || $sourceidentity['familykey'] !== $targetidentity['familykey']
                || !source_registry::enabled($allowedareas, $file)
                || !$course->visible || !$cm->visible || !empty($cm->availability)
                || !$section->visible || !empty($section->availability)
        ) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        $ordinary = can_access_course($course, $USER, '', true) && $provider->readable($cminfo, $file);
        $grant = ($rules->get_config()['publication']['mode'] ?? 'source-access-only') === 'explicit-family-publication'
            && $DB->record_exists(
                'local_filebank_publish',
                ['profileid' => $profile->id, 'cmid' => $cm->id, 'revoked' => 0]
            );
        if (!$ordinary && !$grant) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        return [$file, $cm, $sourceidentity, $course];
    }

    /**
     * Explicitly publish or revoke an eligible source module.
     *
     * @param int $profileid Profile.
     * @param int $cmid Module.
     * @param bool $revoked Revoke flag.
     */
    public static function publish(int $profileid, int $cmid, bool $revoked): void {
        global $DB, $USER;
        [$profile, $revision, $rules] = profiles::load($profileid);
        $cm = get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST);
        $course = get_course($cm->course);
        $section = $DB->get_record('course_sections', ['id' => $cm->section], '*', MUST_EXIST);
        require_capability('local/filebank:publish', \context_course::instance($cm->course));
        require_capability('moodle/course:manageactivities', \context_course::instance($cm->course));
        if (
            !source_registry::provider($cm->modname)
                || (!$revoked && (!$course->visible || !$cm->visible || !empty($cm->availability)
                    || !$section->visible || !empty($section->availability)
                    || ($rules->get_config()['publication']['mode'] ?? 'source-access-only')
                        !== 'explicit-family-publication'))
        ) {
            throw new \moodle_exception('materialunavailable', 'local_filebank');
        }
        $record = $DB->get_record('local_filebank_publish', ['profileid' => $profileid, 'cmid' => $cmid]);
        $data = (object)['profileid' => $profileid, 'cmid' => $cmid, 'userid' => $USER->id,
            'timecreated' => time(), 'revoked' => (int)$revoked];
        if ($record) {
            $data->id = $record->id;
            $DB->update_record('local_filebank_publish', $data);
        } else {
            $DB->insert_record('local_filebank_publish', $data);
        }
        log::record($revoked ? 'publication_revoked' : 'publication_granted', '', ['profileid' => $profileid, 'cmid' => $cmid]);
    }
}
