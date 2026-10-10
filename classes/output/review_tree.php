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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\output;

/**
 * Render reviewed destinations as an accessible nested list with native disclosures.
 */
final class review_tree {
    /**
     * Group a bounded, already authorized review by destination directory.
     *
     * @param array $operations Reviewed operations.
     * @return array Recursive Mustache context.
     */
    public static function data(array $operations): array {
        $root = ['children' => []];
        foreach ($operations as $operation) {
            $node = &$root;
            foreach (array_filter(explode('/', $operation['filepath']), 'strlen') as $part) {
                $key = 'folder:' . $part;
                if (!isset($node['children'][$key])) {
                    $node['children'][$key] = ['name' => $part, 'folder' => true, 'children' => []];
                }
                $node = &$node['children'][$key];
            }
            $key = 'file:' . $operation['filename'] . ':' . ($operation['pathnamehash'] ?? '');
            $previous = $node['children'][$key] ?? null;
            if (
                $previous && ($previous['blocked'] || ($previous['copy']
                    && !str_starts_with($operation['decision'], 'blocked_')))
            ) {
                unset($node);
                continue;
            }
            $node['children'][$key] = [
                'name' => $operation['filename'], 'hash' => $operation['pathnamehash'] ?? '', 'folder' => false,
                'author' => $operation['snapshot']['author'], 'license' => $operation['snapshot']['license'],
                'decision' => display::outcome($operation['decision']),
                'blocked' => str_starts_with($operation['decision'], 'blocked_'),
                'copy' => $operation['decision'] === 'copy',
            ];
            unset($node);
        }
        return self::children($root['children']);
    }

    /**
     * Sort directories before files and normalize associative children for Mustache.
     *
     * @param array $children Directory entries.
     * @return array Sorted node list.
     */
    private static function children(array $children): array {
        uasort($children, static fn($a, $b) => ($b['folder'] <=> $a['folder']) ?: strnatcasecmp($a['name'], $b['name']));
        foreach ($children as &$child) {
            if ($child['folder']) {
                $child['children'] = self::children($child['children']);
            }
        }
        return array_values($children);
    }
}
