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
 * Build file and folder card identities from an already authorized bounded catalog page.
 */
final class browser_cards {
    /**
     * Data.
     *
     * @param array $result Result.
     * @param array $scope Scope.
     * @param array $filters Filters.
     * @param array|null $folder Current navigation folder.
     * @return array Result.
     */
    public static function data(array $result, array $scope, array $filters, ?array $folder): array {
        $cards = [];
        $seen = [];
        $suggestions = [];
        foreach ($result['assets'] as $asset) {
            foreach ($asset['occurrences'] as $origin) {
                self::origin($asset, $origin, $scope, $filters, $folder, $cards, $seen, $suggestions);
            }
        }
        return [$cards, $suggestions];
    }

    /**
     * Origin.
     *
     * @param array $asset Asset.
     * @param array $origin Origin.
     * @param array $scope Scope.
     * @param array $filters Filters.
     * @param array|null $folder Current navigation folder.
     * @param array $cards Cards.
     * @param array $seen Seen.
     * @param array $suggestions Suggestions.
     */
    private static function origin(
        array $asset,
        array $origin,
        array $scope,
        array $filters,
        ?array $folder,
        array &$cards,
        array &$seen,
        array &$suggestions
    ): void {

        if ($scope['contextid'] === (int)\context_module::instance($origin['cmid'])->id) {
            return;
        }
        self::suggestions($origin, $suggestions);
        $path = $folder['filepath'] ?? '/';
        if ($folder && !str_starts_with($origin['filepath'], $path)) {
            return;
        }
        $filecard = ($origin['module'] !== 'folder' || $origin['filearea'] !== 'content')
            || $folder || $filters['q'] !== '';
        self::file($asset, $origin, $filecard, $folder, $path, $cards, $seen);
        self::folder($origin, $filters, $folder, $path, $cards);
    }

    /**
     * File.
     *
     * @param array $asset Asset.
     * @param array $origin Origin.
     * @param bool $filecard Filecard.
     * @param array|null $folder Current navigation folder.
     * @param string $path Path.
     * @param array $cards Cards.
     * @param array $seen Seen.
     */
    private static function file(
        array $asset,
        array $origin,
        bool $filecard,
        ?array $folder,
        string $path,
        array &$cards,
        array &$seen
    ): void {
        if ($filecard && (!$folder || $origin['filepath'] === $path)) {
            $value = 'file:' . $origin['pathnamehash'];
            if (!isset($seen[$asset['contenthash']])) {
                $sourcefile = get_file_storage()->get_file_by_hash($origin['pathnamehash']);
                if (!$sourcefile) {
                    return;
                }
                $cards[$value] = ['value' => $value, 'title' => s($origin['filename']), 'isfolder' => false,
                    'courseid' => $origin['courseid'], 'contenthash' => $sourcefile->get_contenthash(),
                    'coursename' => \local_filebank\output\display::name(
                        $origin['shortname'],
                        \context_course::instance($origin['courseid'])
                    ),
                    'origin' => \local_filebank\output\display::name(
                        $origin['shortname'],
                        \context_course::instance($origin['courseid'])
                    ) . ' / '
                        . \local_filebank\output\display::name(
                            $origin['modulename'],
                            \context_module::instance($origin['cmid'])
                        ),
                    'details' => $origin['author'] . ' / ' . $origin['license'] . ' / ' . display_size($asset['filesize']),
                    'icon' => file_file_icon($sourcefile),
                    'open' => ''];
                $seen[$asset['contenthash']] = true;
            }
        }
    }

    /**
     * Folder.
     *
     * @param array $origin Origin.
     * @param array $filters Filters.
     * @param array|null $folder Current navigation folder.
     * @param string $path Path.
     * @param array $cards Cards.
     */
    private static function folder(array $origin, array $filters, ?array $folder, string $path, array &$cards): void {
        if (
            $origin['module'] === 'folder' && $origin['filearea'] === 'content'
                && $filters['q'] === '' && empty($filters['mimetype'])
        ) {
            if (!$folder) {
                $targetpath = '/';
                $title = \local_filebank\output\display::name(
                    $origin['modulename'],
                    \context_module::instance($origin['cmid'])
                );
            } else {
                $remaining = substr($origin['filepath'], strlen($path));
                if ($remaining === '') {
                    return;
                }
                $name = explode('/', $remaining)[0];
                $targetpath = $path . $name . '/';
                $title = s($name);
            }
            $value = 'folder:' . $origin['cmid'] . ':' . base64_encode($targetpath);
            $cards[$value] = ['value' => $value, 'title' => $title, 'isfolder' => true,
                'courseid' => $origin['courseid'],
                'coursename' => \local_filebank\output\display::name(
                    $origin['shortname'],
                    \context_course::instance($origin['courseid'])
                ),
                'origin' => \local_filebank\output\display::name(
                    $origin['shortname'],
                    \context_course::instance($origin['courseid'])
                ), 'details' => '',
                'open' => $value];
        }
    }

    /**
     * Suggestions.
     *
     * @param array $origin Origin.
     * @param array $suggestions Suggestions.
     */
    private static function suggestions(array $origin, array &$suggestions): void {
        foreach ($origin['captures'] ?? [] as $name => $value) {
            if ($value !== '' && count($suggestions[$name] ?? []) < 100) {
                $suggestions[$name][$value] = $value;
            }
        }
    }
}
