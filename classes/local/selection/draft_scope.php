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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\selection;

use local_multipicker\form\scoped_filemanager;

/**
 * Session-local authorization binding for an explicitly integrated owning form.
 */
final class draft_scope {
    /** @var int Maximum scope lifetime in seconds. */
    public const LIFETIME = 1800;
    /** @var int Maximum active controls per session. */
    public const MAX_SCOPES = 32;

    /**
     * Locate a binding created from an actual owning form, never from request-provided limits.
     *
     * @param \context $context Repository's current context.
     * @param int $draftid Current draft.
     * @param string $sesskey Session key.
     * @return string Matching token, or empty when no native form has registered this draft.
     */
    public static function find(\context $context, int $draftid, string $sesskey): string {
        global $SESSION;
        foreach ($SESSION->local_multipicker_drafts ?? [] as $token => $scope) {
            if ($scope['contextid'] === $context->id && $scope['draftid'] === $draftid) {
                try {
                    self::resolve($token, $sesskey);
                    return $token;
                } catch (\moodle_exception $exception) {
                    continue;
                }
            }
        }
        return '';
    }

    /**
     * Return the actor's current validated binding to server-side consumers.
     *
     * @param string $token Form scope.
     * @param string $sesskey Session key.
     * @return array Current form authority.
     */
    public static function describe(string $token, string $sesskey): array {
        return self::resolve($token, $sesskey);
    }

    /**
     * Store bounded browser state while invalidating changed visible selections.
     *
     * @param string $token Form scope.
     * @param array $view Validated browser state.
     * @param string $sesskey Session key.
     */
    public static function remember_view(string $token, array $view, string $sesskey): void {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        if (isset($scope['review']) && ($scope['view'] ?? []) !== $view) {
            unset($SESSION->local_multipicker_drafts[$token]['review']);
        }
        $SESSION->local_multipicker_drafts[$token]['view'] = $view;
    }

    /**
     * Use the name of the native repository instance that opened this scope.
     *
     * @param string $token Actual form scope.
     * @param string $name Server-loaded repository instance name.
     * @param string $sesskey Session key.
     */
    public static function select_repository(string $token, string $name, string $sesskey): void {
        global $SESSION;
        self::resolve($token, $sesskey);
        $SESSION->local_multipicker_drafts[$token]['repositoryname'] = $name;
    }

    /**
     * Narrow an editor subpicker to its requested types while retaining the native form ceilings.
     *
     * @param string $token Actual form scope.
     * @param array $types Core subpicker extensions; these may only restrict an editor's wildcard contract.
     * @param string $environment Actual native picker environment.
     * @param string $sesskey Session key.
     */
    public static function select_picker(string $token, array $types, string $environment, string $sesskey): void {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        if (($scope['environment'] ?? '') !== 'editor') {
            return;
        }
        $types = (new \core_form\filetypes_util())->normalize_file_types($types) ?: ['*'];
        $links = $environment === 'editor' && in_array('*', $types, true);
        $limit = $environment === 'editor' && !$links ? 1 : material_selection::MAX_FILES;
        if (
            $scope['limits']['accepted_types'] !== $types || ($scope['limits']['selectionlimit'] ?? 0) !== $limit
                || ($scope['editorlinks'] ?? false) !== $links
        ) {
            unset(
                $SESSION->local_multipicker_drafts[$token]['review'],
                $SESSION->local_multipicker_drafts[$token]['view'],
                $SESSION->local_multipicker_drafts[$token]['imported']
            );
        }
        $SESSION->local_multipicker_drafts[$token]['limits']['accepted_types'] = $types;
        $SESSION->local_multipicker_drafts[$token]['limits']['selectionlimit'] = $limit;
        $SESSION->local_multipicker_drafts[$token]['editorlinks'] = $links;
    }

    /**
     * Revalidate an approved selection before handing it to the native repository transfer.
     *
     * @param string $token Form scope.
     * @param string $digest Approval.
     * @param string $sesskey Session key.
     * @return array Scope and current review.
     */
    public static function approved(string $token, string $digest, string $sesskey): array {
        $scope = self::resolve($token, $sesskey);
        $saved = $scope['review'] ?? null;
        if (!$saved || !hash_equals($saved['digest'], $digest)) {
            throw new \moodle_exception('batchconflict', 'local_multipicker');
        }
        $review = draft_import::review(
            $scope['courseid'],
            $saved['profileid'],
            $scope['draftid'],
            $saved['items'],
            $scope['limits'],
            $scope['basepath'],
            $scope['copymode'] ?? 'paths',
            ['roots' => $scope['view']['roots'] ?? [], 'renames' => $scope['view']['renames'] ?? []]
        );
        if ($review['blocked'] || !hash_equals($review['digest'], $digest)) {
            throw new \moodle_exception('batchconflict', 'local_multipicker');
        }
        return [$scope, $review];
    }

    /**
     * Change folder placement only after invalidating any old approval.
     *
     * @param string $token Form scope.
     * @param string $mode Folder mapping mode.
     * @param string $sesskey Session key.
     */
    public static function select_mode(string $token, string $mode, string $sesskey): void {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        if (!in_array($mode, ['paths', 'folder', 'contents'], true)) {
            throw new \moodle_exception('invalidselection', 'local_multipicker');
        }
        if (($scope['copymode'] ?? 'paths') !== $mode) {
            unset($SESSION->local_multipicker_drafts[$token]['review']);
            $SESSION->local_multipicker_drafts[$token]['copymode'] = $mode;
        }
    }

    /**
     * Register a real native form element, never browser-provided options.
     *
     * @param \MoodleQuickForm_filemanager $element Actual owning control.
     * @param \context $context Actual owning form context.
     * @param string $basepath Destination directory.
     * @return string Random session-local token.
     */
    public static function open(\MoodleQuickForm_filemanager $element, \context $context, string $basepath = '/'): string {
        global $SESSION, $USER;
        self::require_context($context, $element->getName());
        $basepath = material_selection::path($basepath);
        $contract = scoped_filemanager::read_contract($element, $context);
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
            throw new \moodle_exception('invaliddraft', 'local_multipicker');
        }
        return self::open_contract($contract, $context, $basepath);
    }

    /**
     * Register limits read from a live native form element by a server-side adapter.
     *
     * @param array $contract Actual native field contract, never request parameters.
     * @param \context $context Actual owning context.
     * @param string $basepath Existing destination path.
     * @return string Random session-local binding.
     */
    public static function open_contract(array $contract, \context $context, string $basepath = '/'): string {
        global $SESSION, $USER;
        self::require_context($context, $contract['field']);
        $basepath = material_selection::path($basepath);
        if (
            $contract['draftid'] <= 0 || !get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $contract['draftid'],
                $basepath,
                '.'
            )
        ) {
            throw new \moodle_exception('invaliddraft', 'local_multipicker');
        }
        $scopes = $SESSION->local_multipicker_drafts ?? [];
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
        $contract['module'] = $context->contextlevel === CONTEXT_MODULE
            ? get_coursemodule_from_id(null, $context->instanceid)->modname : ($contract['module'] ?? '');
        $scopes[$token] = $contract + ['userid' => (int)$USER->id, 'contextid' => $context->id,
            'courseid' => (int)$context->get_course_context()->instanceid, 'basepath' => $basepath,
            'expires' => time() + self::LIFETIME];
        $SESSION->local_multipicker_drafts = $scopes;
        return $token;
    }

    /**
     * Refuse a token from a different form control or a changed native contract.
     *
     * @param string $token Existing binding.
     * @param \MoodleQuickForm_filemanager $element Current live control.
     * @param \context $context Owning context.
     * @param string $sesskey Current session key.
     */
    public static function assert_control(
        string $token,
        \MoodleQuickForm_filemanager $element,
        \context $context,
        string $sesskey
    ): void {
        $scope = self::resolve($token, $sesskey);
        $contract = scoped_filemanager::read_contract($element, $context);
        if (
            $scope['contextid'] !== $context->id || $scope['field'] !== $contract['field']
                || $scope['draftid'] !== $contract['draftid'] || $scope['limits'] !== $contract['limits']
        ) {
            throw new \moodle_exception('formscopeunavailable', 'local_multipicker');
        }
    }

    /**
     * List existing directories in this actor's bound draft, respecting the native subdirectory option.
     *
     * @param string $token Form scope.
     * @param string $sesskey Current session key.
     * @return array Directory paths indexed by path.
     */
    public static function destinations(string $token, string $sesskey): array {
        global $DB, $USER;
        $scope = self::resolve($token, $sesskey);
        $conditions = ['contextid' => \context_user::instance($USER->id)->id, 'component' => 'user',
            'filearea' => 'draft', 'itemid' => $scope['draftid'], 'filename' => '.'];
        if (!$scope['limits']['subdirs']) {
            $conditions['filepath'] = '/';
        }
        $directories = $DB->get_records('files', $conditions, 'filepath', 'id, filepath');
        $paths = array_column($directories, 'filepath');
        return array_combine($paths, $paths);
    }

    /**
     * Bind an existing destination and discard approval whenever that destination changes.
     *
     * @param string $token Form scope.
     * @param string $basepath Submitted destination directory.
     * @param string $sesskey Current session key.
     */
    public static function select_destination(string $token, string $basepath, string $sesskey): void {
        global $SESSION, $USER;
        $scope = self::resolve($token, $sesskey);
        $basepath = material_selection::path($basepath);
        if (!$scope['limits']['subdirs'] && $basepath !== '/') {
            throw new \moodle_exception('batchlimit', 'local_multipicker');
        }
        if (
            !get_file_storage()->get_file(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                $scope['draftid'],
                $basepath,
                '.'
            )
        ) {
            throw new \moodle_exception('invaliddraft', 'local_multipicker');
        }
        if ($scope['basepath'] !== $basepath) {
            unset($SESSION->local_multipicker_drafts[$token]['review']);
            $SESSION->local_multipicker_drafts[$token]['basepath'] = $basepath;
        }
    }

    /**
     * Require the current form fields to describe exactly the approved profile and selection.
     *
     * @param string $token Form scope.
     * @param int $profileid Current profile field.
     * @param array $items Current normalized selection.
     * @param string $digest Submitted approval.
     * @param string $sesskey Current session key.
     */
    public static function assert_review(string $token, int $profileid, array $items, string $digest, string $sesskey): void {
        global $SESSION;
        $scope = self::resolve($token, $sesskey);
        $review = $scope['review'] ?? null;
        if (
            !$review || $review['profileid'] !== $profileid || $review['items'] !== $items
                || !hash_equals($review['digest'], $digest)
        ) {
            unset($SESSION->local_multipicker_drafts[$token]['review']);
            throw new \moodle_exception('batchconflict', 'local_multipicker');
        }
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
        unset($SESSION->local_multipicker_drafts[$token]['review'], $SESSION->local_multipicker_drafts[$token]['imported']);
        $review = draft_import::review(
            $scope['courseid'],
            $profileid,
            $scope['draftid'],
            $items,
            $scope['limits'],
            $scope['basepath'],
            $scope['copymode'] ?? 'paths',
            ['roots' => $scope['view']['roots'] ?? [], 'renames' => $scope['view']['renames'] ?? []]
        );
        $SESSION->local_multipicker_drafts[$token]['review'] = [
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
        unset($SESSION->local_multipicker_drafts[$token]['review']);
        if (!$review || !hash_equals($review['digest'], $digest)) {
            throw new \moodle_exception('batchconflict', 'local_multipicker');
        }
        $result = draft_import::apply(
            $scope['courseid'],
            $review['profileid'],
            $scope['draftid'],
            $review['items'],
            $scope['limits'],
            $digest,
            $scope['basepath'],
            $scope['copymode'] ?? 'paths',
            ['roots' => $scope['view']['roots'] ?? [], 'renames' => $scope['view']['renames'] ?? []]
        );
        $SESSION->local_multipicker_drafts[$token]['imported'] = array_map(static fn($operation) => [
            'filepath' => $operation['filepath'], 'filename' => $operation['filename'],
            'contenthash' => $operation['snapshot']['contenthash'],
        ], $result['operations']);
        return $result;
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
        unset($SESSION->local_multipicker_drafts[$token]);
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
        $scope = $SESSION->local_multipicker_drafts[$token] ?? null;
        if (
            !preg_match('/^[a-f0-9]{64}$/', $token) || !$scope
                || $scope['userid'] !== (int)$USER->id || $scope['expires'] <= time()
        ) {
            throw new \moodle_exception('formscopeunavailable', 'local_multipicker');
        }
        $context = \context::instance_by_id($scope['contextid'], IGNORE_MISSING);
        if (!$context) {
            throw new \moodle_exception('formscopeunavailable', 'local_multipicker');
        }
        self::require_context($context, $scope['field']);
        $course = get_course($scope['courseid']);
        if ((int)$context->get_course_context()->instanceid !== $scope['courseid']) {
            throw new \moodle_exception('formscopeunavailable', 'local_multipicker');
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
     * @param string $field Actual field name.
     */
    private static function require_context(\context $context, string $field = ''): void {
        if (!isloggedin() || isguestuser() || !in_array($context->contextlevel, [CONTEXT_COURSE, CONTEXT_MODULE], true)) {
            throw new \moodle_exception('formscopeunavailable', 'local_multipicker');
        }
        destination_access::require((int)$context->get_course_context()->instanceid, $context, $field);
    }
}
