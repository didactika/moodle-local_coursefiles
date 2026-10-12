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
 * Count unique remaining and selected descendants using one grouped native file query.
 */
final class folder_selection {
    /**
     * Count remaining and selected descendants for authorized visible folders with one grouped query.
     *
     * @param array $scope Validated destination.
     * @param array $selected Canonical file selectors.
     * @param array $values Folder selectors issued by the current browser.
     * @return array Checkbox states and readable counts indexed by folder selector.
     */
    public static function states(array $scope, array $selected, array $values): array {

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
        $records = $DB->get_recordset_sql(
            'SELECT sourcefile.contextid, sourcefile.filepath, sourcefile.contenthash, '
                . $sum . ' AS selected FROM {files} sourcefile WHERE sourcefile.contextid ' . $in
                . ' AND sourcefile.component = :component AND sourcefile.filearea = :filearea'
                . ' AND sourcefile.itemid = 0 AND sourcefile.filename <> :directory' . $exclusion
                . ' GROUP BY sourcefile.contextid, sourcefile.filepath, sourcefile.contenthash',
            $params + $destinationparams
        );
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
            [$total, $checked] = self::counts($folder, $counts);
            $states[$value] = ['value' => $value, 'checked' => $total > 0 && $checked === $total,
                'partial' => $checked > 0 && $checked < $total,
                'selectionlabel' => get_string(
                    'folderselectioncount',
                    'local_filebank',
                    (object)['selected' => $checked, 'total' => $total]
                )];
        }
        return $states;
    }

    /**
     * Counts.
     *
     * @param array $folder Folder.
     * @param array $counts Counts.
     * @return array Result.
     */
    private static function counts(array $folder, array $counts): array {
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
        return [$total, $checked];
    }
}
