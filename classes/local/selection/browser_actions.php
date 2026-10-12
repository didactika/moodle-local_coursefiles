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
 * Apply canonical chooser selection, filters and navigation to an authenticated session binding.
 */
final class browser_actions {
    /**
     * Expand.
     *
     * @param array $scope Scope.
     * @param array $state State.
     * @param string $token Token.
     */
    public static function expand(array $scope, array &$state, string $token): void {
        if (array_filter($state['selected'], static fn($value) => str_starts_with($value, 'folder:'))) {
            $state['roots'] = array_values(
                array_unique(
                    array_merge(
                        $state['roots'],
                        array_values(
                            array_filter(
                                $state['selected'],
                                static fn($value) => str_starts_with($value, 'folder:')
                            )
                        )
                    )
                )
            );
            $files = material_selection::resolve(
                $scope['courseid'],
                $state['profileid'],
                selection_options::parse($state['selected']),
                $scope['draftid']
            );
            $state['selected'] = selected_content::unique(array_map(static fn($hash) => 'file:' . $hash, array_keys($files)));
            draft_state::remember_view($token, $state, sesskey());
        }
    }

    /**
     * Apply.
     *
     * @param array $scope Scope.
     * @param array $state State.
     * @param array $input Input.
     * @param array $profiles Profiles.
     * @param string $token Token.
     */
    public static function apply(array $scope, array &$state, array $input, array $profiles, string $token): void {
        self::profile($state, $input, $profiles);
        self::filters($state, $input);
        if (!empty($input['clear'])) {
            $state['selected'] = [];
            $state['roots'] = [];
            $state['renames'] = [];
        }
        if ($input['toggleitem'] !== '') {
            self::toggle($scope, $state, $input['toggleitem']);
        }
        if (!empty($input['selectall'])) {
            $selection = browser_listing::select_all($scope, $state);
            $state['selected'] = $selection['selected'];
            $state['roots'] = $selection['roots'];
        }
        if (!empty($input['selectpage'])) {
            self::select_page($scope, $state);
        }
        self::navigate($state, $input);
        $state['selected'] = selected_content::unique($state['selected']);
        $selectedhashes = array_fill_keys(
            array_map(
                static fn($value) => substr($value, 5),
                $state['selected']
            ),
            true
        );
        $state['roots'] = array_slice($state['roots'], -material_selection::MAX_FILES);
        $state['renames'] = array_intersect_key($state['renames'], $selectedhashes);
        selection_limits::require_capacity($scope, $state['selected']);
        draft_state::select_destination($token, $input['destination'], sesskey());
        draft_state::select_mode($token, $input['mode'], sesskey());
        draft_state::remember_view($token, $state, sesskey());
    }

    /**
     * Profile.
     *
     * @param array $state State.
     * @param array $input Input.
     * @param array $profiles Profiles.
     */
    private static function profile(array &$state, array $input, array $profiles): void {
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
    }

    /**
     * Filters.
     *
     * @param array $state State.
     * @param array $input Input.
     */
    private static function filters(array &$state, array $input): void {
        [, , $rules] = \local_filebank\local\profile\profiles::load($state['profileid']);
        $captures = $input['captures'];
        $state['filters']['captures'] = array_intersect_key($captures, $rules->filter_definitions());
        foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $filter) {
            $state['filters'][$filter] = $input[$filter];
        }
        if (!empty($input['clearfilters'])) {
            $state['filters'] = ['q' => '', 'edition' => '', 'shortname' => '', 'module' => '',
                'mimetype' => '', 'captures' => []];
        }
    }

    /**
     * Toggle.
     *
     * @param array $scope Scope.
     * @param array $state State.
     * @param string $value Value.
     */
    private static function toggle(array $scope, array &$state, string $value): void {
        $values = [$value];
        $files = material_selection::resolve(
            $scope['courseid'],
            $state['profileid'],
            selection_options::parse($values),
            $scope['draftid']
        );
        $values = array_map(static fn($hash) => 'file:' . $hash, array_keys($files));
        $currentfiles = selected_content::records($state['selected']);
        $candidatehashes = array_unique(array_map(static fn($file) => $file->get_contenthash(), $files));
        $selectedhashes = array_unique(array_column($currentfiles, 'contenthash'));
        if (!array_diff($candidatehashes, $selectedhashes)) {
            $state['selected'] = array_values(
                array_filter(
                    $state['selected'],
                    static fn($value) => !isset($currentfiles[$value])
                        || !in_array($currentfiles[$value]->contenthash, $candidatehashes, true)
                )
            );
        } else {
            $state['selected'] = selected_content::unique(array_merge($state['selected'], $values));
            if (str_starts_with($value, 'folder:')) {
                $state['roots'][] = $value;
                $state['roots'] = array_values(array_unique($state['roots']));
            }
        }
        if (count($state['selected']) > material_selection::MAX_FILES) {
            throw new \moodle_exception('batchlimit', 'local_filebank');
        }
    }

    /**
     * Navigate.
     *
     * @param array $state State.
     * @param array $input Input.
     */
    private static function navigate(array &$state, array $input): void {
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
    }

    /**
     * Select page.
     *
     * @param array $scope Scope.
     * @param array $state State.
     */
    private static function select_page(array $scope, array &$state): void {
        $current = browser_listing::page($scope, $state);
        $state['selected'] = array_values(
            array_unique(
                array_merge(
                    $state['selected'],
                    array_map(
                        static fn($hash) => 'file:' . $hash,
                        array_keys(
                            material_selection::resolve(
                                $scope['courseid'],
                                $state['profileid'],
                                selection_options::parse(array_column($current['cards'], 'value')),
                                $scope['draftid']
                            )
                        )
                    )
                )
            )
        );
        selection_options::parse($state['selected']);
        $state['roots'] = array_values(
            array_unique(
                array_merge(
                    $state['roots'],
                    array_column(
                        array_filter($current['cards'], static fn($card) => $card['isfolder']),
                        'value'
                    )
                )
            )
        );
    }
}
