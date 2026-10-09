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
 * Session binding for reviewed native draft batches.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

use local_coursebridge\form\scoped_filemanager;

/**
 * Session-local authorization binding for an explicitly integrated owning form.
 */
final class draft_scope {
    /** @var int Maximum scope lifetime in seconds. */
    public const LIFETIME = 1800;
    /** @var int Maximum active controls per session. */
    public const MAX_SCOPES = 32;

    /**
     * Register a real native form element, never browser-provided options.
     *
     * @param scoped_filemanager $element Actual owning control.
     * @param \context $context Actual owning form context.
     * @param string $basepath Destination directory.
     * @return string Random session-local token.
     */
    public static function open(scoped_filemanager $element, \context $context, string $basepath = '/'): string {
        global $SESSION, $USER;
        self::require_context($context);
        $basepath = material_selection::path($basepath);
        $contract = $element->get_contract($context);
        if ($contract['draftid'] <= 0) {
            $element->setValue(file_get_unused_draft_itemid());
            $contract['draftid'] = (int)$element->getValue();
            get_file_storage()->create_directory(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $contract['draftid'],
                '/'
            );
        }
        if (
            !get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $contract['draftid'],
                $basepath,
                '.'
            )
        ) {
            throw new \moodle_exception('invaliddraft', 'local_coursebridge');
        }
        $scopes = $SESSION->local_coursebridge_drafts ?? [];
        foreach ($scopes as $key => $scope) {
            if (
                $scope['expires'] <= time() || $scope['userid'] !== (int)$USER->id
                    || ($scope['contextid'] === $context->id && $scope['field'] === $contract['field']
                        && $scope['draftid'] === $contract['draftid'])
            ) {
                unset($scopes[$key]);
            }
        }
        while (count($scopes) >= self::MAX_SCOPES) {
            array_shift($scopes);
        }
        $token = bin2hex(random_bytes(32));
        $scopes[$token] = $contract + ['userid' => (int)$USER->id, 'contextid' => $context->id,
            'courseid' => (int)$context->get_course_context()->instanceid, 'basepath' => $basepath,
            'expires' => time() + self::LIFETIME];
        $SESSION->local_coursebridge_drafts = $scopes;
        return $token;
    }

    /**
     * Review selectors against the stored form contract and remember only the resulting approval.
     *
     * @param string $token Form scope.
     * @param int $profileid Enabled relationship profile.
     * @param array $items Source selection.
     * @param string $sesskey Current session key.
     * @return array Batch decisions and digest.
     */
    public static function review(string $token, int $profileid, array $items, string $sesskey): array {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        unset($SESSION->local_coursebridge_drafts[$token]['review']);
        $review = draft_import::review(
            $scope['courseid'],
            $profileid,
            $scope['draftid'],
            $items,
            $scope['limits'],
            $scope['basepath']
        );
        $SESSION->local_coursebridge_drafts[$token]['review'] = [
            'profileid' => $profileid, 'items' => $items, 'digest' => $review['digest'],
        ];
        return $review;
    }

    /**
     * Apply exactly the session's reviewed selection without accepting destination or limit overrides.
     *
     * @param string $token Form scope.
     * @param string $digest Approved review.
     * @param string $sesskey Current session key.
     * @return array Applied decisions.
     */
    public static function apply(string $token, string $digest, string $sesskey): array {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        $review = $scope['review'] ?? null;
        unset($SESSION->local_coursebridge_drafts[$token]['review']);
        if (!$review || !hash_equals($review['digest'], $digest)) {
            throw new \moodle_exception('batchconflict', 'local_coursebridge');
        }
        return draft_import::apply(
            $scope['courseid'],
            $review['profileid'],
            $scope['draftid'],
            $review['items'],
            $scope['limits'],
            $digest,
            $scope['basepath']
        );
    }

    /**
     * Explicitly release an integrated control on owning-form cancellation or completion.
     *
     * @param string $token Form scope.
     * @param string $sesskey Current session key.
     */
    public static function close(string $token, string $sesskey): void {
        global $SESSION;
        self::resolve($token, $sesskey);
        unset($SESSION->local_coursebridge_drafts[$token]);
    }

    /**
     * Authenticate the session binding and recheck current context and upload ceilings.
     *
     * @param string $token Form scope.
     * @param string $sesskey Session key.
     * @return array Current authority.
     */
    private static function resolve(string $token, string $sesskey): array {
        global $CFG, $SESSION, $USER;
        if (!confirm_sesskey($sesskey)) {
            throw new \moodle_exception('invalidsesskey');
        }
        $scope = $SESSION->local_coursebridge_drafts[$token] ?? null;
        if (
            !preg_match('/^[a-f0-9]{64}$/', $token) || !$scope
                || $scope['userid'] !== (int)$USER->id || $scope['expires'] <= time()
        ) {
            throw new \moodle_exception('formscopeunavailable', 'local_coursebridge');
        }
        $context = \context::instance_by_id($scope['contextid'], IGNORE_MISSING);
        if (!$context) {
            throw new \moodle_exception('formscopeunavailable', 'local_coursebridge');
        }
        self::require_context($context);
        $course = get_course($scope['courseid']);
        if ((int)$context->get_course_context()->instanceid !== $scope['courseid']) {
            throw new \moodle_exception('formscopeunavailable', 'local_coursebridge');
        }
        $scope['limits']['maxbytes'] = get_user_max_upload_file_size(
            $context,
            $CFG->maxbytes,
            $course->maxbytes,
            $scope['limits']['maxbytes']
        );
        return $scope;
    }

    /**
     * Restrict scope creation/use to authorized course editors in course or module contexts.
     *
     * @param \context $context Owning form context.
     */
    private static function require_context(\context $context): void {
        if (!isloggedin() || isguestuser() || !in_array($context->contextlevel, [CONTEXT_COURSE, CONTEXT_MODULE], true)) {
            throw new \moodle_exception('formscopeunavailable', 'local_coursebridge');
        }
        require_capability('local/coursebridge:reuse', $context->get_course_context());
        require_capability('moodle/course:manageactivities', $context);
    }
}
