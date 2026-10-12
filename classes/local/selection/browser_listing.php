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

use local_filebank\local\catalog\catalog;

/**
 * Presents bounded authorized file and folder cards.
 */
final class browser_listing {
    /**
     * Select all currently filtered results across pages, preserving the synchronous batch bound.
     *
     * @param array $scope Validated destination binding.
     * @param array $state Current filters, folder and selection.
     * @return array Canonical selected files and placement roots.
     */
    public static function select_all(array $scope, array $state): array {

        $state['after'] = '';
        $selected = array_fill_keys($state['selected'], true);
        $roots = array_fill_keys($state['roots'] ?? [], true);
        do {
            $page = self::page($scope, $state);
            if ($page['cards']) {
                $values = array_column($page['cards'], 'value');
                $files = material_selection::resolve(
                    $scope['courseid'],
                    $state['profileid'],
                    selection_options::parse($values),
                    $scope['draftid'] ?? 0
                );
                foreach (array_keys($files) as $hash) {
                    $selected['file:' . $hash] = true;
                }
                foreach ($page['cards'] as $card) {
                    if ($card['isfolder']) {
                        $roots[$card['value']] = true;
                    }
                }
                $selected = array_fill_keys(selected_content::unique(array_keys($selected)), true);
                selection_limits::require_capacity($scope, array_keys($selected));
                if (count($selected) > material_selection::MAX_FILES || count($roots) > material_selection::MAX_FILES) {
                    throw new \moodle_exception('batchlimit', 'local_filebank');
                }
            }
            $state['after'] = $page['next'];
        } while ($state['after'] !== '');
        return ['selected' => array_keys($selected), 'roots' => array_keys($roots)];
    }

    /**
     * Build a navigable page without treating deduplicated content groups as folders.
     *
     * @param array $scope Validated destination binding.
     * @param array $state Browser filters, cursor and selected values.
     * @return array Cards, retained selections, pagination and parent link.
     */
    public static function page(array $scope, array $state): array {

        $filters = $state['filters'];
        $filters['excludedcontext'] = $scope['contextid'];
        $folder = null;
        if ($state['folder'] !== '') {
            $folder = selection_options::parse([$state['folder']])[0];
            if (!isset($folder['cmid'])) {
                throw new \moodle_exception('invalidselection', 'local_filebank');
            }
            $filters['cmid'] = $folder['cmid'];
            $filters['sourcepath'] = $folder['filepath'];
        }
        $result = catalog::search($scope['courseid'], $state['profileid'], $filters, $state['after'], 50, $scope['draftid'] ?? 0);
        [$cards, $suggestions] = browser_cards::data($result, $scope, $filters, $folder);
        uasort(
            $cards,
            static fn($left, $right) => ($right['isfolder'] <=> $left['isfolder'])
                ?: strnatcasecmp($left['title'], $right['title'])
        );
        $selectedcontent = array_unique(array_column(selected_content::records($state['selected']), 'contenthash'));
        $selecteditems = array_map(static fn($value) => ['value' => $value], $state['selected']);
        $retained = array_values(array_filter($selecteditems, static fn($item) => !isset($cards[$item['value']])));
        $capacity = selection_limits::capacity($scope);
        $foldervalues = array_unique(
            array_merge(
                $state['treefolders'] ?? [],
                array_column(array_filter($cards, static fn($card) => $card['isfolder']), 'value')
            )
        );
        $folderstates = folder_selection::states($scope, $state['selected'], $foldervalues);
        self::decorate($cards, $state, $capacity, $folderstates, $selectedcontent, $folder);
        $groups = [];
        foreach ($cards as $card) {
            $id = $card['courseid'];
            $groups[$id]['name'] = $card['coursename'];
            $groups[$id]['cards'][] = $card;
        }
        $parent = '';
        if ($folder && $folder['filepath'] !== '/') {
            $parentpath = dirname(rtrim($folder['filepath'], '/'));
            $parent = 'folder:' . $folder['cmid'] . ':' . base64_encode($parentpath === '/' ? '/' : $parentpath . '/');
        }
        return ['cards' => array_values($cards), 'treegroups' => array_values($groups), 'suggestions' => $suggestions,
            'folderstates' => json_encode(array_values($folderstates)),
            'selectedcontent' => json_encode(array_values($selectedcontent)),
            'retained' => $retained, 'selecteditems' => $selecteditems, 'next' => $result['next'],
            'location' => $folder ? \local_filebank\output\display::name(
                get_course(get_coursemodule_from_id('folder', $folder['cmid'])->course)->shortname
            ) . ' / ' . s($folder['filepath']) : '',
            'parent' => $parent, 'infolder' => $folder !== null, 'path' => $folder['filepath'] ?? '',
            'count' => count($state['selected'])];
    }

    /**
     * Decorate.
     *
     * @param array $cards Cards.
     * @param array $state State.
     * @param int $capacity Capacity.
     * @param array $folderstates Folderstates.
     * @param array $selectedcontent Selectedcontent.
     * @param array|null $folder Current navigation folder.
     */
    private static function decorate(
        array &$cards,
        array $state,
        int $capacity,
        array $folderstates,
        array $selectedcontent,
        ?array $folder
    ): void {
        foreach ($cards as &$card) {
            $card['plaintitle'] = strip_tags(html_entity_decode($card['title'], ENT_QUOTES, 'UTF-8'));
            $card['selectionfull'] = count($state['selected']) >= $capacity;
            if ($card['isfolder']) {
                $card += $folderstates[$card['value']];
            } else {
                $card['checked'] = in_array($card['contenthash'], $selectedcontent, true);
            }
            $card['showorigin'] = !$folder;
            $card['id'] = 'cbitem-' . sha1($card['value']);
        }
        unset($card);
    }
}
