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
            'missing_target' => $data['missingtarget'] ?? 'create',
            'same_path_different_content' => $data['conflictpolicy'] ?? 'block', 'correspondences' => []];
        foreach (row_data::decode($data['correspondencesjson'] ?? '[]', 20) as $row) {
            $rule = self::correspondence($row);
            $config['correspondences'][] = $rule;
        }
        if (
            !$config['correspondences'] || !in_array($config['section_match']['strategy'], ['ordinal', 'name', 'mapping'], true)
            || !in_array($config['missing_target'], ['block', 'create', 'skip'], true)
            || !in_array($config['same_path_different_content'], ['block', 'overwrite', 'skip'], true)
            || !in_array($config['same_content_different_path'], ['skip-and-report', 'keep'], true)
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        self::mapping($config, $data);
        return $config;
    }

    /**
     * Decode the rules as the migration screens send them.
     *
     * @param array $ui The rules: correspondences, sectionstrategy, sectionmap and the three policies.
     * @return array Validated migration rules.
     */
    public static function from_ui(array $ui): array {
        return self::from_form([
            'correspondencesjson' => json_encode(array_values($ui['correspondences'] ?? [])),
            'sectionmapjson' => json_encode(array_values($ui['sectionmap'] ?? [])),
            'sectionstrategy' => $ui['sectionstrategy'] ?? 'ordinal',
            'duplicatepolicy' => $ui['duplicatepolicy'] ?? 'skip-and-report',
            'missingtarget' => $ui['missingtarget'] ?? 'create',
            'conflictpolicy' => $ui['conflictpolicy'] ?? 'block',
        ]);
    }

    /**
     * Describe stored migration rules the way the migration screens edit them.
     *
     * @param array $config Stored migration rules; with none, there is no correspondence and the person adds them.
     * @return array The rules: correspondences, sectionstrategy, sectionmap and the three policies.
     */
    public static function to_ui(array $config): array {
        $rows = [];
        // A profile can describe its single correspondence directly, without the list.
        $rules = $config['correspondences'] ?? (isset($config['source_module'], $config['target_module']) ? [$config] : []);
        foreach ($rules as $rule) {
            $row = [];
            foreach (['source', 'target'] as $side) {
                $predicate = $rule[$side . '_module'] ?? ['equals' => ''];
                if ($side === 'source') {
                    $row['sourcetype'] = $predicate['type'] ?? 'folder';
                }
                $method = isset($predicate['pattern']) ? 'pattern' : (isset($predicate['template']) ? 'template' : 'equals');
                $row[$side . 'field'] = $predicate['field'] ?? 'name';
                $row[$side . 'method'] = $method;
                $row[$side . 'value'] = (string) ($predicate[$method] ?? '');
            }
            $rows[] = $row;
        }
        $map = [];
        foreach ($config['section_match']['map'] ?? [] as $source => $target) {
            $map[] = ['source' => (string) $source, 'target' => (string) $target];
        }
        return ['correspondences' => $rows, 'sectionstrategy' => $config['section_match']['strategy'] ?? 'ordinal',
            'sectionmap' => $map, 'duplicatepolicy' => $config['same_content_different_path'] ?? 'skip-and-report',
            'missingtarget' => $config['missing_target'] ?? 'create',
            'conflictpolicy' => ($config['same_path_different_content'] ?? '') === 'overwrite' ? 'overwrite' : 'skip'];
    }

    /**
     * Correspondence.
     *
     * @param array $row Row.
     * @return array Result.
     */
    private static function correspondence(array $row): array {
        $rule = [];
        foreach (['source', 'target'] as $side) {
            $rule[$side . '_module'] = self::module($row, $side);
        }
        return $rule;
    }

    /**
     * Module.
     *
     * @param array $row Row.
     * @param string $side Side.
     * @return array Result.
     */
    private static function module(array $row, string $side): array {

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
        self::pattern($method, $value);
        $type = $side === 'source' ? ($row['sourcetype'] ?? 'folder') : 'folder';
        if (!is_string($type) || !\local_filebank\local\catalog\source_registry::provider($type)) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        return ['type' => $type, 'field' => $field, $method => trim($value)];
    }

    /**
     * Mapping.
     *
     * @param array $config Config.
     * @param array $data Data.
     */
    private static function mapping(array &$config, array $data): void {
        if ($config['section_match']['strategy'] !== 'mapping') {
            return;
        }
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

    /**
     * Pattern.
     *
     * @param string $method Method.
     * @param string $value Value.
     */
    private static function pattern(string $method, string $value): void {
        if (
            $method === 'pattern' && (!in_array($value[0], ['~', '#', '/'], true)
            || @preg_match($value, '') === false)
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
}
