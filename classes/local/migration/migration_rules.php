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
 * Capture scope and layout selection before preview.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\migration;

use local_filebank\form\row_data;

/**
 * Validates migration-owned folder correspondences and conflict choices.
 */
final class migration_rules {
    /**
     * Decode native form values into the reviewed migration document.
     *
     * @param array $data Native form values.
     * @return array Validated migration rules.
     */
    public static function from_form(array $data): array {
        $config = ['identical_content' => 'skip', 'section_match' => ['strategy' => $data['sectionstrategy'] ?? 'ordinal'],
            'same_content_different_path' => $data['duplicatepolicy'] ?? 'skip-and-report',
            'missing_target' => $data['missingtarget'] ?? 'block',
            'same_path_different_content' => $data['conflictpolicy'] ?? 'block', 'correspondences' => []];
        foreach (row_data::decode($data['correspondencesjson'] ?? '[]', 20) as $row) {
            $rule = [];
            foreach (['source', 'target'] as $side) {
                $field = $row[$side . 'field'] ?? '';
                $method = $row[$side . 'method'] ?? '';
                $value = $row[$side . 'value'] ?? '';
                if (
                    !is_string($value) || trim($value) === '' || strlen($value) > 2048
                    || !in_array($field, ['name', 'idnumber'], true)
                    || !in_array($method, ['equals', 'pattern', 'template'], true)
                ) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                if (
                    $method === 'pattern' && (!in_array($value[0], ['~', '#', '/'], true)
                    || @preg_match($value, '') === false)
                ) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                $type = $side === 'source' ? ($row['sourcetype'] ?? 'folder') : 'folder';
                if (!is_string($type) || !\local_filebank\local\catalog\source_registry::provider($type)) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                $rule[$side . '_module'] = ['type' => $type, 'field' => $field, $method => trim($value)];
            }
            $config['correspondences'][] = $rule;
        }
        if (
            !$config['correspondences'] || !in_array($config['section_match']['strategy'], ['ordinal', 'name', 'mapping'], true)
            || !in_array($config['missing_target'], ['block', 'create'], true)
            || !in_array($config['same_path_different_content'], ['block', 'overwrite'], true)
            || !in_array($config['same_content_different_path'], ['skip-and-report', 'keep'], true)
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if ($config['section_match']['strategy'] === 'mapping') {
            $config['section_match']['map'] = [];
            foreach (row_data::decode($data['sectionmapjson'] ?? '[]', 200) as $row) {
                $source = $row['source'] ?? '';
                $target = $row['target'] ?? '';
                if (
                    !ctype_digit((string)$source) || !ctype_digit((string)$target) || (int)$source <= 0 || (int)$target <= 0
                    || isset($config['section_match']['map'][(string)$source])
                ) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                $config['section_match']['map'][(string)$source] = (int)$target;
            }
        }
        return $config;
    }
}
