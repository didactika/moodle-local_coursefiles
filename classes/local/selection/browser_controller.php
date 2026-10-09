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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\selection;

use local_multipicker\output\display;

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
        $input = [];
        foreach (['q', 'edition', 'shortname', 'module', 'mimetype', 'layout', 'mode'] as $name) {
            if (!is_string($raw[$name] ?? '') || strlen($raw[$name] ?? '') > 1024) {
                throw new \moodle_exception('invalidselection', 'local_multipicker');
            }
            $input[$name] = clean_param($raw[$name] ?? '', PARAM_TEXT);
        }
        $input['mode'] = $input['mode'] ?: 'paths';
        $input['profileid'] = clean_param($raw['profileid'] ?? 0, PARAM_INT);
        $items = $raw['items'] ?? [];
        $captures = $raw['captures'] ?? [];
        if (
            !is_array($items) || count($items) > material_selection::MAX_FILES || !is_array($captures)
                || count($captures) > 12 || array_filter($items, static fn($item) => !is_string($item))
                || array_filter($captures, static fn($value) => !is_string($value) || strlen($value) > 255)
        ) {
            throw new \moodle_exception('invalidselection', 'local_multipicker');
        }
        $input['items'] = clean_param_array($items, PARAM_RAW);
        $input['captures'] = clean_param_array($captures, PARAM_TEXT);
        $input['removeitem'] = clean_param($raw['removeitem'] ?? '', PARAM_RAW);
        $input['destination'] = material_selection::path($raw['destination'] ?? '/');
        foreach (['search', 'clear', 'selectpage', 'review'] as $name) {
            $input[$name] = clean_param($raw[$name] ?? false, PARAM_BOOL);
        }
        foreach (['openfolder', 'previouspage', 'next'] as $name) {
            if (array_key_exists($name, $raw)) {
                $input[$name] = clean_param($raw[$name], PARAM_RAW);
            }
        }
        return $input;
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
        $profiles = $DB->get_records_menu('local_multipicker', ['enabled' => 1], 'name', 'id, name');
        $state = $scope['view'] ?? ['profileid' => (int)array_key_first($profiles), 'selected' => [],
            'filters' => ['q' => '', 'edition' => '', 'shortname' => '', 'module' => $scope['module'] ?? '', 'mimetype' => ''],
            'folder' => '', 'after' => '', 'history' => []];
        $state['history'] = $state['history'] ?? [];
        $state['layout'] = $state['layout'] ?? 'grid';
        $capturecontrols = [];
        $message = '';

        try {
            if ($input !== null) {
                $state['selected'] = array_values(array_unique($input['items']));
                selection_options::parse($state['selected']);
                $profileid = $input['profileid'];
                if (!isset($profiles[$profileid])) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
                if ($profileid !== (int)$state['profileid']) {
                    $state['selected'] = [];
                    $state['folder'] = '';
                    $state['after'] = '';
                    $state['history'] = [];
                }
                $state['profileid'] = $profileid;
                $layout = $input['layout'];
                $state['layout'] = in_array($layout, ['grid', 'list'], true) ? $layout : 'grid';
                $removed = $input['removeitem'];
                if ($removed !== '') {
                    $state['selected'] = array_values(array_diff($state['selected'], [$removed]));
                }
                [, , $rules] = \local_multipicker\local\profile\profiles::load($profileid);
                $captures = $input['captures'];
                $state['filters']['captures'] = array_intersect_key($captures, $rules->filter_definitions());
                foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $filter) {
                    $state['filters'][$filter] = $input[$filter];
                }
                if (!empty($input['clear'])) {
                    $state['selected'] = [];
                }
                if (!empty($input['selectpage'])) {
                    $current = browser_listing::page($scope, $state);
                    $state['selected'] = array_values(array_unique(array_merge(
                        $state['selected'],
                        array_column($current['cards'], 'value')
                    )));
                    selection_options::parse($state['selected']);
                }
                if (!empty($input['search'])) {
                    $state['after'] = '';
                    $state['history'] = [];
                    $state['folder'] = '';
                } else if (array_key_exists('openfolder', $input)) {
                    $state['folder'] = $input['openfolder'];
                    $state['after'] = '';
                    $state['history'] = [];
                } else if (array_key_exists('previouspage', $input)) {
                    $state['after'] = array_pop($state['history']) ?? '';
                } else if (array_key_exists('next', $input)) {
                    $state['history'][] = $state['after'];
                    $state['history'] = array_slice($state['history'], -32);
                    $state['after'] = $input['next'];
                }
                draft_scope::select_destination($token, $input['destination'], sesskey());
                draft_scope::select_mode($token, $input['mode'], sesskey());
                draft_scope::remember_view($token, $state, sesskey());
                if (!empty($input['review'])) {
                    return ['review' => true, 'data' => [], 'message' => ''];
                }
            }
            $scope = draft_scope::describe($token, sesskey());
            $listing = browser_listing::page($scope, $state);
            $capturecontrols = \local_multipicker\output\capture_filters::data(
                (int)$state['profileid'],
                $state['filters']['captures'] ?? [],
                $listing['suggestions']
            );
            $listing['previouspage'] = (bool)$state['history'];
        } catch (\moodle_exception $exception) {
            $message = $OUTPUT->notification(display::reason($exception->errorcode), 'error');
            $listing = ['cards' => [], 'retained' => array_map(static fn($value) => ['value' => $value], $state['selected']),
                'next' => '', 'parent' => '', 'infolder' => false, 'count' => count($state['selected'])];
        }
        return ['review' => false, 'message' => $message, 'data' => $listing + [
        'contextid' => $scope['contextid'],
        'layout' => $state['layout'],
        'listlayout' => $state['layout'] === 'list',
        'action' => (new \moodle_url('/local/multipicker/selection/index.php', ['scope' => $token]))->out(false),
        'sesskey' => sesskey(), 'scope' => $token,
        'multipleprofiles' => count($profiles) > 1, 'profileid' => $state['profileid'],
        'profiles' => display::options($profiles, (string)$state['profileid']),
        'capturefilters' => array_slice($capturecontrols, 0, 2),
        'morecapturefilters' => array_slice($capturecontrols, 2),
        'filters' => $state['filters'], 'modules' => display::options([
        '' => get_string('all'),
        ] + \local_multipicker\local\catalog\source_registry::module_names(), $state['filters']['module']),
        'types' => display::options(display::filetypes(), $state['filters']['mimetype']),
        'destinations' => display::options(draft_scope::destinations($token, sesskey()), $scope['basepath']),
        'modes' => display::options([
        'paths' => get_string('copypaths', 'local_multipicker'), 'folder' => get_string('copyfolder', 'local_multipicker'),
        'contents' => get_string('copycontents', 'local_multipicker'),
        ], $scope['copymode'] ?? 'paths'),
        'locked' => \local_multipicker\local\policy\author_policy::locked($course->id),
        ]];
    }
}
