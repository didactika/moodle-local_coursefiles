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
 * Readable relationship and placement rules.
 *
 * @package local_coursefiles
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursefiles\output;

/**
 * Exports rule descriptions without HTML or configuration changes.
 */
final class profile_summary {
    /**
     * Describe a validated configuration.
     *
     * @param array $config Profile document.
     * @param bool $placement Include migration placement rules.
     * @return array Template context.
     */
    public static function data(array $config, bool $placement = true): array {
        $migration = $config['migration'] ?? [];
        $rows = [];
        foreach ($migration['correspondences'] ?? [$migration] as $correspondence) {
            foreach (['source_module', 'target_module'] as $name) {
                $selector = $correspondence[$name] ?? [];
                $rows[] = ['label' => get_string($name, 'local_coursefiles'),
                'value' => implode(' / ', array_filter([
                    $selector['type'] ?? 'folder', $selector['field'] ?? 'name',
                    $selector['equals'] ?? $selector['template'] ?? $selector['pattern']
                        ?? get_string('nomatchcondition', 'local_coursefiles'),
                ]))];
            }
        }
        $strategy = $migration['section_match']['strategy'] ?? 'ordinal';
        $rows[] = ['label' => get_string('sectionmatch', 'local_coursefiles'),
            'value' => get_string('sectionstrategy_' . $strategy, 'local_coursefiles')];
        $rows[] = ['label' => get_string('duplicaterule', 'local_coursefiles'),
            'value' => get_string(($migration['same_content_different_path'] ?? 'skip-and-report') === 'keep'
                ? 'duplicatekeep' : 'duplicateskip', 'local_coursefiles')];
        $rows[] = ['label' => get_string('conflictrule', 'local_coursefiles'),
            'value' => get_string(($migration['same_path_different_content'] ?? 'block') === 'overwrite'
                ? 'conflictoverwrite' : 'conflictblock', 'local_coursefiles')];
        $rules = new \local_coursefiles\local\profile\profile_config($config);
        $filters = [];
        foreach ($rules->filter_definitions() as $definition) {
            $filters[] = ['label' => display::label($definition['label']), 'capture' => $definition['capture']];
        }
        return ['rows' => $placement ? $rows : [], 'placement' => $placement,
            'filters' => $filters, 'name' => display::name($config['name'] ?? ''),
            'parsers' => $config['relationship']['parsers'] ?? [],
            'familykey' => $config['relationship']['family_key'] ?? '',
            'editioncapture' => $config['relationship']['edition']['capture'] ?? 'edition'];
    }
}
