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
 * CourseBridge native material import integration.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

use local_coursebridge\local\catalog\catalog;

/**
 * Presents bounded authorized file and folder cards.
 */
final class browser_listing {
    /**
     * Build a navigable page without treating deduplicated content groups as folders.
     *
     * @param array $scope Validated destination binding.
     * @param array $state Browser filters, cursor and selected values.
     * @return array Cards, retained selections, pagination and parent link.
     */
    public static function page(array $scope, array $state): array {
        $filters = $state['filters'];
        $folder = null;
        if ($state['folder'] !== '') {
            $folder = selection_options::parse([$state['folder']])[0];
            if (!isset($folder['cmid'])) {
                throw new \moodle_exception('invalidselection', 'local_coursebridge');
            }
            $filters['cmid'] = $folder['cmid'];
            $filters['sourcepath'] = $folder['filepath'];
        }
        $result = catalog::search($scope['courseid'], $state['profileid'], $filters, $state['after'], 50);
        $cards = [];
        $seen = [];
        foreach ($result['assets'] as $asset) {
            foreach ($asset['occurrences'] as $origin) {
                if ($scope['contextid'] === (int)\context_module::instance($origin['cmid'])->id) {
                    continue;
                }
                $path = $folder['filepath'] ?? '/';
                if ($folder && !str_starts_with($origin['filepath'], $path)) {
                    continue;
                }
                $filecard = $origin['module'] !== 'folder' || $folder || $filters['q'] !== '';
                if ($filecard && (!$folder || $origin['filepath'] === $path)) {
                    $value = 'file:' . $origin['pathnamehash'];
                    if (!isset($seen[$asset['contenthash']])) {
                        $cards[$value] = ['value' => $value, 'title' => $origin['filename'], 'isfolder' => false,
                            'origin' => $origin['shortname'] . ' / ' . $origin['modulename'],
                            'details' => $origin['author'] . ' / ' . $origin['license'] . ' / ' . display_size($asset['filesize']),
                            'icon' => get_file_storage()->get_file_by_hash($origin['pathnamehash'])->get_mimetype(),
                            'open' => ''];
                        $seen[$asset['contenthash']] = true;
                    }
                }
                if ($origin['module'] === 'folder') {
                    if (!$folder) {
                        $targetpath = '/';
                        $title = $origin['modulename'];
                    } else {
                        $remaining = substr($origin['filepath'], strlen($path));
                        if ($remaining === '') {
                            continue;
                        }
                        $name = explode('/', $remaining)[0];
                        $targetpath = $path . $name . '/';
                        $title = $name;
                    }
                    $value = 'folder:' . $origin['cmid'] . ':' . base64_encode($targetpath);
                    $cards[$value] = ['value' => $value, 'title' => $title, 'isfolder' => true,
                        'origin' => $origin['shortname'], 'details' => $targetpath,
                        'open' => $value];
                }
            }
        }
        uasort($cards, static fn($left, $right) => ($right['isfolder'] <=> $left['isfolder'])
            ?: strnatcasecmp($left['title'], $right['title']));
        $retained = [];
        foreach ($state['selected'] as $value) {
            if (!isset($cards[$value])) {
                $retained[] = ['value' => $value];
            }
        }
        foreach ($cards as &$card) {
            $card['checked'] = in_array($card['value'], $state['selected'], true);
            $card['id'] = 'cbitem-' . sha1($card['value']);
        }
        unset($card);
        $parent = '';
        if ($folder && $folder['filepath'] !== '/') {
            $parentpath = dirname(rtrim($folder['filepath'], '/'));
            $parent = 'folder:' . $folder['cmid'] . ':' . base64_encode($parentpath === '/' ? '/' : $parentpath . '/');
        }
        return ['cards' => array_values($cards), 'retained' => $retained, 'next' => $result['next'],
            'parent' => $parent, 'infolder' => $folder !== null, 'path' => $folder['filepath'] ?? '',
            'count' => count($state['selected'])];
    }
}
