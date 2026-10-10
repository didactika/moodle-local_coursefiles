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
 * Native profile fields backed by an existing validated document.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\profile;

/**
 * Preserves advanced keys while editing the commonly used rules.
 */
final class guided_config {
    /**
     * Export defaults for the guided controls.
     *
     * @param array $config Saved rules.
     * @return array Form defaults.
     */
    public static function defaults(array $config): array {
        $defaults = [
            'displaytemplate' => $config['relationship']['display_label'] ?? '${family}',
            'editioncapture' => $config['relationship']['edition']['capture'] ?? '',
            'editionoptional' => !empty($config['relationship']['edition']['optional']),
            'pairdimensions' => implode(', ', $config['relationship']['pair_dimensions'] ?? []),
            'categoryids' => $config['scope']['category_ids'] ?? [],
            'publicationmode' => $config['publication']['mode'] ?? 'source-access-only',
            'sectionstrategy' => $config['migration']['section_match']['strategy'] ?? 'ordinal',
            'duplicatepolicy' => $config['migration']['same_content_different_path'] ?? 'skip-and-report',
        ];
        foreach (['source', 'target'] as $side) {
            $predicate = $config['migration'][$side . '_module'] ?? [];
            $kind = isset($predicate['pattern']) ? 'pattern' : (isset($predicate['template']) ? 'template' : 'equals');
            $defaults[$side . 'folderfield'] = $predicate['field'] ?? 'name';
            $defaults[$side . 'foldermatch'] = $kind;
            $defaults[$side . 'foldervalue'] = $predicate[$kind] ?? '';
        }
        return $defaults;
    }

    /**
     * Apply defined form fields only; never erase rules that have no guided control.
     *
     * @param array $config Original document.
     * @param array $data Native form values.
     * @return array Updated document.
     */
    public static function apply(array $config, array $data): array {
        if (array_key_exists('displaytemplate', $data)) {
            $label = trim($data['displaytemplate']);
            if ($label === '') {
                unset($config['relationship']['display_label']);
            } else {
                $config['relationship']['display_label'] = $label;
            }
        }
        if (array_key_exists('editioncapture', $data)) {
            $capture = trim($data['editioncapture']);
            if ($capture === '') {
                unset($config['relationship']['edition']);
            } else {
                $config['relationship']['edition'] = ['capture' => $capture,
                    'optional' => !empty($data['editionoptional']), 'comparison' => 'string'];
            }
        }
        if (array_key_exists('pairdimensions', $data)) {
            $dimensions = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['pairdimensions'])))));
            foreach ($dimensions as $dimension) {
                if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $dimension)) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
            $config['relationship']['pair_dimensions'] = $dimensions;
        }
        if (!empty($data['guidedsettings'])) {
            $config['scope']['category_ids'] = array_values(array_unique(array_map('intval', $data['categoryids'] ?? [])));
            foreach (
                ['publicationmode' => ['source-access-only', 'explicit-family-publication'],

                ] as $key => $values
            ) {
                if (!in_array($data[$key] ?? '', $values, true)) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
            $config['publication']['mode'] = $data['publicationmode'];
            if (isset($data['sectionstrategy'], $data['duplicatepolicy'])) {
                $config['migration']['section_match']['strategy'] = $data['sectionstrategy'];
                $config['migration']['same_content_different_path'] = $data['duplicatepolicy'];
            }
        }
        return $config;
    }
}
