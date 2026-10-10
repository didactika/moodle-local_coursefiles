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

namespace local_multipicker\local\selection;

use local_multipicker\local\catalog\catalog;

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
                foreach ($files as $hash => $file) {
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
                    throw new \moodle_exception('batchlimit', 'local_multipicker');
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
                throw new \moodle_exception('invalidselection', 'local_multipicker');
            }
            $filters['cmid'] = $folder['cmid'];
            $filters['sourcepath'] = $folder['filepath'];
        }
        $result = catalog::search($scope['courseid'], $state['profileid'], $filters, $state['after'], 50, $scope['draftid'] ?? 0);
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
                $filecard = ($origin['module'] !== 'folder' || $origin['filearea'] !== 'content')
                    || $folder || $filters['q'] !== '';
                if ($filecard && (!$folder || $origin['filepath'] === $path)) {
                    $value = 'file:' . $origin['pathnamehash'];
                    if (!isset($seen[$asset['contenthash']])) {
                        $sourcefile = get_file_storage()->get_file_by_hash($origin['pathnamehash']);
                        if (!$sourcefile) {
                            continue;
                        }
                        $cards[$value] = ['value' => $value, 'title' => s($origin['filename']), 'isfolder' => false,
                            'courseid' => $origin['courseid'], 'contenthash' => $sourcefile->get_contenthash(),
                            'coursename' => \local_multipicker\output\display::name(
                                $origin['shortname'],
                                \context_course::instance($origin['courseid'])
                            ),
                            'origin' => \local_multipicker\output\display::name(
                                $origin['shortname'],
                                \context_course::instance($origin['courseid'])
                            ) . ' / '
                                . \local_multipicker\output\display::name(
                                    $origin['modulename'],
                                    \context_module::instance($origin['cmid'])
                                ),
                            'details' => $origin['author'] . ' / ' . $origin['license'] . ' / ' . display_size($asset['filesize']),
                            'icon' => file_file_icon($sourcefile),
                            'open' => ''];
                        $seen[$asset['contenthash']] = true;
                    }
                }
                if (
                    $origin['module'] === 'folder' && $origin['filearea'] === 'content'
                        && $filters['q'] === '' && empty($filters['mimetype'])
                ) {
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
                        'courseid' => $origin['courseid'],
                        'coursename' => \local_multipicker\output\display::name(
                            $origin['shortname'],
                            \context_course::instance($origin['courseid'])
                        ),
                        'origin' => \local_multipicker\output\display::name(
                            $origin['shortname'],
                            \context_course::instance($origin['courseid'])
                        ), 'details' => '',
                        'open' => $value];
                }
            }
        }
        uasort($cards, static fn($left, $right) => ($right['isfolder'] <=> $left['isfolder'])
            ?: strnatcasecmp($left['title'], $right['title']));
        $selectedcontent = array_unique(array_column(selected_content::records($state['selected']), 'contenthash'));
        $selecteditems = array_map(static fn($value) => ['value' => $value], $state['selected']);
        $retained = array_values(array_filter($selecteditems, static fn($item) => !isset($cards[$item['value']])));
        $capacity = selection_limits::capacity($scope);
        $foldervalues = array_unique(array_merge(
            $state['treefolders'] ?? [],
            array_column(array_filter($cards, static fn($card) => $card['isfolder']), 'value')
        ));
        $folderstates = self::folder_states($scope, $state['selected'], $foldervalues);
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
            'location' => $folder ? \local_multipicker\output\display::name(
                get_course(get_coursemodule_from_id('folder', $folder['cmid'])->course)->shortname
            ) . ' / ' . s($folder['filepath']) : '',
            'parent' => $parent, 'infolder' => $folder !== null, 'path' => $folder['filepath'] ?? '',
            'count' => count($state['selected'])];
    }

    /**
     * Count remaining and selected descendants for authorized visible folders with one grouped query.
     *
     * @param array $scope Validated destination.
     * @param array $selected Canonical file selectors.
     * @param array $values Folder selectors issued by the current browser.
     * @return array Checkbox states and readable counts indexed by folder selector.
     */
    private static function folder_states(array $scope, array $selected, array $values): array {
        global $DB;
        if (!$values) {
            return [];
        }
        $folders = [];
        $contexts = [];
        foreach ($values as $value) {
            $item = selection_options::parse([$value])[0];
            $id = \context_module::instance($item['cmid'])->id;
            $contexts[$id] = $id;
            $folders[$value] = ['contextid' => $id, 'path' => $item['filepath']];
        }
        [$in, $params] = $DB->get_in_or_equal(array_values($contexts), SQL_PARAMS_NAMED, 'foldercontext');
        $params += ['component' => 'mod_folder', 'filearea' => 'content', 'directory' => '.'];
        $sum = '0';
        $selectedhashes = array_unique(array_column(selected_content::records($selected), 'contenthash'));
        if ($selectedhashes) {
            [$selectedin, $selectedparams] = $DB->get_in_or_equal(
                $selectedhashes,
                SQL_PARAMS_NAMED,
                'selectedhash'
            );
            $params += $selectedparams;
            $sum = 'MAX(CASE WHEN sourcefile.contenthash ' . $selectedin . ' THEN 1 ELSE 0 END)';
        }
        [$exclusion, $destinationparams] = destination_content::exclusion('sourcefile', $scope['draftid'] ?? 0);
        $records = $DB->get_recordset_sql('SELECT sourcefile.contextid, sourcefile.filepath, sourcefile.contenthash, '
            . $sum . ' AS selected FROM {files} sourcefile WHERE sourcefile.contextid ' . $in
            . ' AND sourcefile.component = :component AND sourcefile.filearea = :filearea'
            . ' AND sourcefile.itemid = 0 AND sourcefile.filename <> :directory' . $exclusion
            . ' GROUP BY sourcefile.contextid, sourcefile.filepath, sourcefile.contenthash', $params + $destinationparams);
        $counts = [];
        try {
            foreach ($records as $record) {
                $counts[$record->contextid][] = $record;
            }
        } finally {
            $records->close();
        }
        $states = [];
        foreach ($folders as $value => $folder) {
            $all = $included = [];
            foreach ($counts[$folder['contextid']] ?? [] as $record) {
                if (str_starts_with($record->filepath, $folder['path'])) {
                    $all[$record->contenthash] = true;
                    if ($record->selected) {
                        $included[$record->contenthash] = true;
                    }
                }
            }
            $total = count($all);
            $checked = count($included);
            $states[$value] = ['value' => $value, 'checked' => $total > 0 && $checked === $total,
                'partial' => $checked > 0 && $checked < $total,
                'selectionlabel' => get_string(
                    'folderselectioncount',
                    'local_multipicker',
                    (object)['selected' => $checked, 'total' => $total]
                )];
        }
        return $states;
    }
}
