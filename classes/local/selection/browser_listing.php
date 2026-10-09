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

use local_multipicker\local\catalog\catalog;

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
        $filters['excludedcontext'] = $scope['contextid'];
        $folder = null;
        if ($state['folder'] !== '') {
            $folder = selection_options::parse([$state['folder']])[0];
            if (!isset($folder['cmid'])) {
                throw new \moodle_exception('invalidselection', 'local_multipicker');
            }
            $filters['cmid'] = $folder['cmid'];
            $filters['sourcepath'] = $folder['filepath'];
        }
        $result = catalog::search($scope['courseid'], $state['profileid'], $filters, $state['after'], 50);
        $cards = [];
        $seen = [];
        $suggestions = [];
        foreach ($result['assets'] as $asset) {
            foreach ($asset['occurrences'] as $origin) {
                if ($scope['contextid'] === (int)\context_module::instance($origin['cmid'])->id) {
                    continue;
                }
                foreach ($origin['captures'] ?? [] as $name => $value) {
                    if ($value !== '' && count($suggestions[$name] ?? []) < 100) {
                        $suggestions[$name][$value] = $value;
                    }
                }
                $path = $folder['filepath'] ?? '/';
                if ($folder && !str_starts_with($origin['filepath'], $path)) {
                    continue;
                }
                $filecard = ($origin['module'] !== 'folder' || $origin['filearea'] !== 'content') || $folder || $filters['q'] !== '';
                if ($filecard && (!$folder || $origin['filepath'] === $path)) {
                    $value = 'file:' . $origin['pathnamehash'];
                    if (!isset($seen[$asset['contenthash']])) {
                        $cards[$value] = ['value' => $value, 'title' => s($origin['filename']), 'isfolder' => false,
                            'origin' => \local_multipicker\output\display::name(
                                $origin['shortname'],
                                \context_course::instance($origin['courseid'])
                            ) . ' / '
                                . \local_multipicker\output\display::name(
                                    $origin['modulename'],
                                    \context_module::instance($origin['cmid'])
                                ),
                            'details' => $origin['author'] . ' / ' . $origin['license'] . ' / ' . display_size($asset['filesize']),
                            'icon' => get_file_storage()->get_file_by_hash($origin['pathnamehash'])->get_mimetype(),
                            'open' => ''];
                        $seen[$asset['contenthash']] = true;
                    }
                }
                if ($origin['module'] === 'folder' && $origin['filearea'] === 'content' && $filters['q'] === '' && empty($filters['mimetype'])) {
                    if (!$folder) {
                        $targetpath = '/';
                        $title = \local_multipicker\output\display::name(
                            $origin['modulename'],
                            \context_module::instance($origin['cmid'])
                        );
                    } else {
                        $remaining = substr($origin['filepath'], strlen($path));
                        if ($remaining === '') {
                            continue;
                        }
                        $name = explode('/', $remaining)[0];
                        $targetpath = $path . $name . '/';
                        $title = s($name);
                    }
                    $value = 'folder:' . $origin['cmid'] . ':' . base64_encode($targetpath);
                    $cards[$value] = ['value' => $value, 'title' => $title, 'isfolder' => true,
                        'origin' => \local_multipicker\output\display::name(
                            $origin['shortname'],
                            \context_course::instance($origin['courseid'])
                        ), 'details' => $targetpath,
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
        return ['cards' => array_values($cards), 'suggestions' => $suggestions,
            'retained' => $retained, 'next' => $result['next'],
            'parent' => $parent, 'infolder' => $folder !== null, 'path' => $folder['filepath'] ?? '',
            'count' => count($state['selected'])];
    }
}
