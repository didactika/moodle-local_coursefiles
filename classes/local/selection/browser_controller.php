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
        $input = [];
        foreach (['q', 'edition', 'shortname', 'module', 'mimetype', 'layout', 'mode'] as $name) {
            if (!is_string($raw[$name] ?? '') || strlen($raw[$name] ?? '') > 1024) {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
            $input[$name] = clean_param($raw[$name] ?? '', PARAM_TEXT);
        }
        $input['mode'] = $input['mode'] ?: 'paths';
        $input['profileid'] = clean_param($raw['profileid'] ?? 0, PARAM_INT);
        $items = $raw['items'] ?? [];
        $captures = $raw['captures'] ?? [];
        if (
            !is_array($items) || count($items) > material_selection::MAX_FILES || !is_array($captures)
                || count($captures) > 9 || array_filter($items, static fn($item) => !is_string($item))
                || array_filter($captures, static fn($value) => !is_string($value) || strlen($value) > 255)
        ) {
            throw new \moodle_exception('invalidselection', 'local_filebank');
        }
        $input['items'] = clean_param_array($items, PARAM_RAW);
        $input['captures'] = clean_param_array($captures, PARAM_TEXT);
        $input['toggleitem'] = clean_param($raw['toggleitem'] ?? '', PARAM_RAW);
        $input['removeitem'] = clean_param($raw['removeitem'] ?? '', PARAM_RAW);
        $input['destination'] = material_selection::path($raw['destination'] ?? '/');
        foreach (['search', 'refresh', 'clear', 'clearfilters', 'selectpage', 'selectall', 'review'] as $name) {
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
            if (array_filter($state['selected'], static fn($value) => str_starts_with($value, 'folder:'))) {
                $state['roots'] = array_values(array_unique(array_merge($state['roots'], array_values(array_filter(
                    $state['selected'],
                    static fn($value) => str_starts_with($value, 'folder:')
                )))));
                $files = material_selection::resolve(
                    $scope['courseid'],
                    $state['profileid'],
                    selection_options::parse($state['selected']),
                    $scope['draftid']
                );
                $state['selected'] = selected_content::unique(array_map(static fn($hash) => 'file:' . $hash, array_keys($files)));
                draft_scope::remember_view($token, $state, sesskey());
            }
            if ($input !== null) {
                $state['selected'] = array_values(array_unique($input['items']));
                selection_options::parse($state['selected']);
                $profileid = $input['profileid'];
                if (!isset($profiles[$profileid])) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                if ($profileid !== (int)$state['profileid']) {
                    $state['selected'] = [];
                    $state['roots'] = [];
                    $state['renames'] = [];
                    $state['folder'] = '';
                    $state['after'] = '';
                    $state['history'] = [];
                    $state['treefolders'] = [];
                }
                $state['profileid'] = $profileid;
                $layout = $input['layout'];
                $state['layout'] = in_array($layout, ['grid', 'list', 'tree'], true) ? $layout : 'grid';
                $removed = $input['removeitem'];
                if ($removed !== '') {
                    $state['selected'] = array_values(array_diff($state['selected'], [$removed]));
                }
                [, , $rules] = \local_filebank\local\profile\profiles::load($profileid);
                $captures = $input['captures'];
                $state['filters']['captures'] = array_intersect_key($captures, $rules->filter_definitions());
                foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $filter) {
                    $state['filters'][$filter] = $input[$filter];
                }
                if (!empty($input['clearfilters'])) {
                    $state['filters'] = ['q' => '', 'edition' => '', 'shortname' => '', 'module' => '',
                        'mimetype' => '', 'captures' => []];
                }
                if (!empty($input['clear'])) {
                    $state['selected'] = [];
                    $state['roots'] = [];
                    $state['renames'] = [];
                }
                if ($input['toggleitem'] !== '') {
                    $values = [$input['toggleitem']];
                    $files = material_selection::resolve(
                        $scope['courseid'],
                        $profileid,
                        selection_options::parse($values),
                        $scope['draftid']
                    );
                    $values = array_map(static fn($hash) => 'file:' . $hash, array_keys($files));
                    $currentfiles = selected_content::records($state['selected']);
                    $candidatehashes = array_unique(array_map(static fn($file) => $file->get_contenthash(), $files));
                    $selectedhashes = array_unique(array_column($currentfiles, 'contenthash'));
                    if (!array_diff($candidatehashes, $selectedhashes)) {
                        $state['selected'] = array_values(array_filter(
                            $state['selected'],
                            static fn($value) => !isset($currentfiles[$value])
                                || !in_array($currentfiles[$value]->contenthash, $candidatehashes, true)
                        ));
                    } else {
                        $state['selected'] = selected_content::unique(array_merge($state['selected'], $values));
                        if (str_starts_with($input['toggleitem'], 'folder:')) {
                            $state['roots'][] = $input['toggleitem'];
                            $state['roots'] = array_values(array_unique($state['roots']));
                        }
                    }
                    if (count($state['selected']) > material_selection::MAX_FILES) {
                        throw new \moodle_exception('batchlimit', 'local_filebank');
                    }
                }
                if (!empty($input['selectall'])) {
                    $selection = browser_listing::select_all($scope, $state);
                    $state['selected'] = $selection['selected'];
                    $state['roots'] = $selection['roots'];
                }
                if (!empty($input['selectpage'])) {
                    $current = browser_listing::page($scope, $state);
                    $state['selected'] = array_values(array_unique(array_merge(
                        $state['selected'],
                        array_map(static fn($hash) => 'file:' . $hash, array_keys(material_selection::resolve(
                            $scope['courseid'],
                            $profileid,
                            selection_options::parse(array_column($current['cards'], 'value')),
                            $scope['draftid']
                        )))
                    )));
                    selection_options::parse($state['selected']);
                    $state['roots'] = array_values(array_unique(array_merge($state['roots'], array_column(
                        array_filter($current['cards'], static fn($card) => $card['isfolder']),
                        'value'
                    ))));
                }
                if (!empty($input['search'])) {
                    $state['after'] = '';
                    $state['history'] = [];
                    $state['folder'] = '';
                } else if (!empty($input['refresh']) || !empty($input['clearfilters'])) {
                    $state['after'] = '';
                    $state['history'] = [];
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
                $state['selected'] = selected_content::unique($state['selected']);
                $selectedhashes = array_fill_keys(array_map(
                    static fn($value) => substr($value, 5),
                    $state['selected']
                ), true);
                $state['roots'] = array_slice($state['roots'], -material_selection::MAX_FILES);
                $state['renames'] = array_intersect_key($state['renames'], $selectedhashes);
                selection_limits::require_capacity($scope, $state['selected']);
                draft_scope::select_destination($token, $input['destination'], sesskey());
                draft_scope::select_mode($token, $input['mode'], sesskey());
                draft_scope::remember_view($token, $state, sesskey());
            }
            $scope = draft_scope::describe($token, sesskey());
            if ($state['selected']) {
                $remaining = destination_content::filter(array_fill_keys(array_map(
                    static fn($value) => substr($value, 5),
                    $state['selected']
                ), true), $scope['draftid']);
                $state['selected'] = array_values(array_filter(
                    $state['selected'],
                    static fn($value) => str_starts_with($value, 'folder:') || isset($remaining[substr($value, 5)])
                ));
            }
            if (!empty($input['review']) && $state['selected']) {
                draft_scope::remember_view($token, $state, sesskey());
                return ['review' => true, 'data' => [], 'message' => ''];
            }
            $listing = browser_listing::page($scope, $state);
            $state['treeroots'] = array_column(array_filter($listing['cards'], static fn($card) => $card['isfolder']), 'value');
            $state['treefolders'] = array_slice(array_values(array_unique(array_merge(
                $state['treefolders'],
                array_column(array_filter($listing['cards'], static fn($card) => $card['isfolder']), 'value')
            ))), -material_selection::MAX_FILES);
            draft_scope::remember_view($token, $state, sesskey());
            $capturecontrols = \local_filebank\output\capture_filters::data(
                (int)$state['profileid'],
                $state['filters']['captures'] ?? [],
                $listing['suggestions']
            );
            $listing['previouspage'] = (bool)$state['history'];
            $catalogpending = false;
            if (empty($listing['cards']) && isset($profiles[$state['profileid']])) {
                [$profile, , $rules] = \local_filebank\local\profile\profiles::load((int)$state['profileid']);
                $catalogpending = !$DB->record_exists(
                    'local_filebank_course',
                    ['courseid' => $course->id, 'revisionid' => $profile->activeid]
                ) && $rules->resolve($course) !== null;
            }
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
        return ['review' => false, 'message' => $message, 'data' => $listing + [

        'filenamehelp' => $OUTPUT->help_icon('filenamefilter', 'local_filebank'),
        'modulehelp' => $OUTPUT->help_icon('modulefilter', 'local_filebank'),
        'filetypehelp' => $OUTPUT->help_icon('filetypefilter', 'local_filebank'),
        'capturehelp' => $OUTPUT->help_icon('capturefilter', 'local_filebank'),
        'profilehelp' => $OUTPUT->help_icon('browserprofile', 'local_filebank'),
        'coursehelp' => $OUTPUT->help_icon('shortnamefilter', 'local_filebank'),
        'destinationhelp' => $OUTPUT->help_icon('batchdestination', 'local_filebank'),
        'modehelp' => $OUTPUT->help_icon('copymode', 'local_filebank'),
        'filtercount' => count(array_filter(array_merge(
            array_intersect_key($state['filters'], array_flip(['q', 'shortname', 'module', 'mimetype', 'edition'])),
            $state['filters']['captures'] ?? []
        ), static fn($value) => trim((string)$value) !== '')),
        'catalogpending' => $catalogpending, 'selectioncapacity' => selection_limits::capacity($scope),
        'contextid' => $scope['contextid'],
        'repositoryname' => display::name($scope['repositoryname'] ?? get_string('pluginname', 'local_filebank')),
        'layout' => $state['layout'],
        'reactlayout' => json_encode([
            'initialLayout' => $state['layout'],
            'views' => array_map(static fn($layout, $icon) => [
                'value' => $layout,
                'label' => get_string('layout' . $layout, 'local_filebank'),
                'icon' => $OUTPUT->pix_icon($icon, '', 'core'),
            ], ['grid', 'list', 'tree'], ['a/view_icon_active', 'a/view_list_active', 'a/view_tree_active']),
        ], JSON_THROW_ON_ERROR),
        'listlayout' => $state['layout'] === 'list',
        'treelayout' => $state['layout'] === 'tree',
        'gridlayout' => $state['layout'] === 'grid',
        'action' => (new \moodle_url('/local/filebank/selection/index.php', ['scope' => $token]))->out(false),
        'sesskey' => sesskey(), 'scope' => $token,
        'multipleprofiles' => count($profiles) > 1, 'profileid' => $state['profileid'],
        'profiles' => display::options($profiles, (string)$state['profileid']),
        'capturefilters' => array_slice($capturecontrols, 0, 2),
        'morecapturefilters' => array_slice($capturecontrols, 2),
        'filters' => $state['filters'], 'modules' => display::options([
        '' => get_string('all'),
        ] + \local_filebank\local\catalog\source_registry::module_names(), $state['filters']['module']),
        'types' => display::options(display::filetypes(), $state['filters']['mimetype']),
        'destinations' => display::options(draft_scope::destinations($token, sesskey()), $scope['basepath']),
        'modes' => display::options([
        'paths' => get_string('copypaths', 'local_filebank'), 'folder' => get_string('copyfolder', 'local_filebank'),
        'contents' => get_string('copycontents', 'local_filebank'),
        ], $scope['copymode'] ?? 'paths'),
        'locked' => \local_filebank\local\policy\author_policy::locked($course->id),
        ]];
    }
}
