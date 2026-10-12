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
 * FileBank native material import integration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\local\selection;

use local_filebank\output\display;

/**
 * Share browser state and presentation between native form requests and Moodle fragments.
 */
final class browser_controller {
    /**
     * Normalize bounded form fields without accepting native limits or destination context IDs.
     *
     * @param array $raw Actual form or fragment fields.
     * @return array Cleaned selection and filter actions.
     */
    public static function input(array $raw): array {
        return browser_input::parse($raw);
    }

    /**
     * Resolve the actual owning page context and update only its session-local selection.
     *
     * @param string $token Native owning scope.
     * @param array|null $input Normalized request, or null for a read-only browser view.
     * @return array Template data, readable notification and optional review navigation.
     */
    public static function data(string $token, ?array $input = null): array {

        global $DB, $OUTPUT;
        $scope = draft_scope::describe($token, sesskey());
        $course = get_course($scope['courseid']);
        $profiles = $DB->get_records_menu('local_filebank', ['enabled' => 1], 'name', 'id, name');
        $state = $scope['view'] ?? ['profileid' => (int)array_key_first($profiles), 'selected' => [],
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => $scope['module'] ?? '', 'mimetype' => ''],
            'folder' => '', 'after' => '', 'history' => []];
        $state['roots'] = $state['roots'] ?? [];
        $state['renames'] = $state['renames'] ?? [];
        $state['history'] = $state['history'] ?? [];
        $state['layout'] = $state['layout'] ?? 'grid';
        $state['treefolders'] = $state['treefolders'] ?? [];
        $capturecontrols = [];
        $message = '';
        $originalstate = $state;

        try {
            browser_actions::expand($scope, $state, $token);
            if ($input !== null) {
                browser_actions::apply($scope, $state, $input, $profiles, $token);
            }
            $scope = draft_scope::describe($token, sesskey());
            if ($state['selected']) {
                $remaining = destination_content::filter(
                    array_fill_keys(
                        array_map(
                            static fn($value) => substr($value, 5),
                            $state['selected']
                        ),
                        true
                    ),
                    $scope['draftid']
                );
                $state['selected'] = array_values(
                    array_filter(
                        $state['selected'],
                        static fn($value) => str_starts_with($value, 'folder:') || isset($remaining[substr($value, 5)])
                    )
                );
            }
            if (!empty($input['review']) && $state['selected']) {
                draft_state::remember_view($token, $state, sesskey());
                return ['review' => true, 'data' => [], 'message' => ''];
            }
            [$listing, $capturecontrols] = browser_view::prepare($token, $scope, $state);
            $catalogpending = self::catalog_pending($listing, $profiles, $state, $course);
        } catch (\moodle_exception $exception) {
            $state = $originalstate;
            $catalogpending = false;
            $message = $OUTPUT->notification(display::reason($exception->errorcode), 'error');
            $listing = ['cards' => [], 'selecteditems' => array_map(static fn($value) => ['value' => $value], $state['selected']),
                'retained' => array_map(static fn($value) => ['value' => $value], $state['selected']),
                'next' => '', 'parent' => '', 'infolder' => false, 'count' => count($state['selected'])];
            if ($exception->errorcode === 'selectionfull') {
                $listing = browser_listing::page($scope, $state);
            }
        }
        return ['review' => false, 'message' => $message,
            'data' => browser_view::data($token, $scope, $state, $listing, $profiles, $capturecontrols, $catalogpending)];
    }

    /**
     * Catalog pending.
     *
     * @param array $listing Listing.
     * @param array $profiles Profiles.
     * @param array $state State.
     * @param \stdClass $course Course.
     * @return bool Result.
     */
    private static function catalog_pending(array $listing, array $profiles, array $state, \stdClass $course): bool {
        global $DB;
        $catalogpending = false;
        if (empty($listing['cards']) && isset($profiles[$state['profileid']])) {
            [$profile, , $rules] = \local_filebank\local\profile\profiles::load((int)$state['profileid']);
            $catalogpending = !$DB->record_exists(
                'local_filebank_course',
                ['courseid' => $course->id, 'revisionid' => $profile->activeid]
            ) && $rules->resolve($course) !== null;
        }
        return $catalogpending;
    }
}
