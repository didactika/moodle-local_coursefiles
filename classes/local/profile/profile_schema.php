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
 * Validate document structure, course parsers and supported relationship fields.
 */
final class profile_schema {
    /**
     * Check.
     *
     * @param array $config Declarative profile document.
     */
    public static function check(array $config): void {
        self::header($config);
        self::sections($config);
        foreach ($config['relationship']['parsers'] as $parser) {
            self::parser($parser);
        }
        self::fields($config);
    }
    /**
     * Header.
     *
     * @param array $config Declarative profile document.
     */
    private static function header(array $config): void {
        if (
            ($config['schema_version'] ?? null) !== 1 || !is_array($config['relationship'] ?? null)
                || empty($config['relationship']['parsers']) || !is_array($config['relationship']['parsers'])
                || empty($config['relationship']['family_key'])
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
    /**
     * Sections.
     *
     * @param array $config Declarative profile document.
     */
    private static function sections(array $config): void {
        foreach (['scope', 'publication', 'migration', 'relationship'] as $name) {
            if (isset($config[$name]) && !is_array($config[$name])) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        if (
            count($config['relationship']['parsers']) > 32
                || !is_string($config['relationship']['family_key'])
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if (
            array_diff(
                array_keys($config),
                ['schema_version', 'status', 'name', 'scope', 'relationship', 'publication', 'migration']
            )
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
    }
    /**
     * Parser.
     *
     * @param mixed $parser Submitted course parser.
     */
    private static function parser($parser): void {
        if (
            !is_array($parser) || !in_array($parser['field'] ?? '', ['idnumber', 'shortname', 'fullname'], true)
                || !is_string($parser['pattern'] ?? null) || strlen($parser['pattern']) > 2048
        ) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        foreach (['required_captures', 'normalizers'] as $key) {
            if (isset($parser[$key]) && !is_array($parser[$key])) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        profile_pattern::match($parser['pattern'], '');
        self::normalizers($parser);
    }
    /**
     * Normalizers.
     *
     * @param array $parser Validated course parser.
     */
    private static function normalizers(array $parser): void {
        foreach (($parser['normalizers'] ?? []) as $normalizers) {
            if (!is_array($normalizers) || array_diff($normalizers, ['trim', 'lowercase', 'uppercase'])) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
    }
    /**
     * Fields.
     *
     * @param array $config Declarative profile document.
     */
    private static function fields(array $config): void {
        foreach (['edition', 'pair_dimensions', 'filters'] as $key) {
            if (isset($config['relationship'][$key]) && !is_array($config['relationship'][$key])) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        if (isset($config['publication']['allowed_sources']) && !is_array($config['publication']['allowed_sources'])) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if (!in_array($config['relationship']['ambiguity_policy'] ?? 'block', ['block', 'first-match'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        foreach (
            ['relationship' => ['ambiguity_policy', 'parsers', 'family_key', 'display_label',
                'edition', 'pair_dimensions', 'filters'],
                'scope' => ['campus', 'category_ids'],
                'publication' => ['mode', 'allowed_sources', 'allow_hidden', 'allow_restricted']] as $name => $keys
        ) {
            if (array_diff(array_keys($config[$name] ?? []), $keys)) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
    }
}
