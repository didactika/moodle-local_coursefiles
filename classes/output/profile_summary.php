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
 * Readable relationship and placement rules.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\output;

/**
 * Exports rule descriptions without HTML or configuration changes.
 */
final class profile_summary {
    /**
     * Describe a validated configuration.
     *
     * @param array $config Profile document.
     * @return array Template context.
     */
    public static function data(array $config): array {
        $migration = $config['migration'] ?? [];
        $rows = [];
        foreach (['source_module', 'target_module'] as $name) {
            $selector = $migration[$name] ?? [];
            $rows[] = ['label' => get_string($name, 'local_multipicker'),
                'value' => implode(' / ', array_filter([
                    $selector['type'] ?? 'folder', $selector['field'] ?? 'name',
                    $selector['equals'] ?? $selector['template'] ?? $selector['pattern']
                        ?? get_string('nomatchcondition', 'local_multipicker'),
                ]))];
        }
        $strategy = $migration['section_match']['strategy'] ?? 'ordinal';
        $rows[] = ['label' => get_string('sectionmatch', 'local_multipicker'),
            'value' => get_string('sectionstrategy_' . $strategy, 'local_multipicker')];
        $rows[] = ['label' => get_string('duplicaterule', 'local_multipicker'),
            'value' => get_string(($migration['same_content_different_path'] ?? 'skip-and-report') === 'keep'
                ? 'duplicatekeep' : 'duplicateskip', 'local_multipicker')];
        $rows[] = ['label' => get_string('conflictrule', 'local_multipicker'),
            'value' => get_string('conflictblock', 'local_multipicker')];
        $rules = new \local_multipicker\local\profile\profile_config($config);
        $filters = [];
        foreach ($rules->filter_definitions() as $definition) {
            $filters[] = ['label' => display::name($definition['label']), 'capture' => $definition['capture']];
        }
        return ['rows' => $rows, 'filters' => $filters, 'name' => display::name($config['name'] ?? ''),
            'parsers' => $config['relationship']['parsers'] ?? [],
            'familykey' => $config['relationship']['family_key'] ?? '',
            'editioncapture' => $config['relationship']['edition']['capture'] ?? 'edition'];
    }
}
