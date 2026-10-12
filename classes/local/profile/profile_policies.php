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
 * Bounded declarative course relationship rules.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\local\profile;

/**
 * Validate public sources, migration choices and bounded capture filter definitions.
 */
final class profile_policies {
    /**
     * Check.
     *
     * @param array $config Declarative profile document.
     */
    public static function check(array $config): void {
        self::publication($config);
        self::migration($config);
    }
    /**
     * Publication.
     *
     * @param array $config Public source settings.
     */
    private static function publication(array $config): void {
        if (
            !in_array(
                $config['publication']['mode'] ?? 'source-access-only',
                ['source-access-only', 'explicit-family-publication'],
                true
            )
                || !empty($config['publication']['allow_hidden']) || !empty($config['publication']['allow_restricted'])
                || array_diff(
                    $config['publication']['allowed_sources'] ?? ['mod_folder/content', 'mod_resource/content'],
                    \local_filebank\local\catalog\source_registry::keys()
                )
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
    /**
     * Migration.
     *
     * @param array $config Optional legacy migration rules.
     */
    private static function migration(array $config): void {
        $migration = $config['migration'] ?? [];
        foreach (
            ['missing_target' => ['block', 'create'], 'ambiguous_target' => ['block'],
                'same_path_same_content' => ['skip'], 'same_path_different_content' => ['block', 'overwrite'],
                'same_content_different_path' => ['skip-and-report', 'keep'],
                'on_item_failure' => ['continue-unaffected']] as $name => $allowed
        ) {
            if (isset($migration[$name]) && !in_array($migration[$name], $allowed, true)) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        foreach (['preserve_relative_paths', 'preserve_source', 'preserve_target_visibility'] as $name) {
            if (isset($migration[$name]) && $migration[$name] !== true) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        if (!in_array($migration['section_match']['strategy'] ?? 'ordinal', ['ordinal', 'name', 'mapping'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
    /**
     * Filters.
     *
     * @param array $definitions Stable configurable capture filters.
     */
    public static function filters(array $definitions): void {
        foreach ($definitions as $name => $definition) {
            self::filter($name, $definition);
        }
        if (count($definitions) > 9) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
    /**
     * Filter.
     *
     * @param mixed $name Submitted stable filter key.
     * @param mixed $definition Submitted capture filter.
     */
    private static function filter($name, $definition): void {

        if (
            !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name)
                || !is_array($definition)
                || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $definition['capture'] ?? '')
                || array_diff(array_keys($definition), ['capture', 'label', 'optional'])
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if (isset($definition['optional']) && !is_bool($definition['optional'])) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        self::filter_label($definition);
    }

    /**
     * Filter label.
     *
     * @param array $definition Definition.
     */
    private static function filter_label(array $definition): void {
        if (!is_string($definition['label'] ?? null) || $definition['label'] === '' || strlen($definition['label']) > 255) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
}
