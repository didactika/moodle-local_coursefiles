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
 * Profile editor with validated unsaved previews.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Validate guided and advanced profile documents independently of the native form layout.
 */
final class profile_document {
    /**
     * Validate a complete profile document without saving a version.
     *
     * @param array $data Submitted native fields.
     * @param array $base Server-loaded document.
     * @return array Result.
     */
    public static function document(array $data, array $base = []): array {
        $config = self::decode($data, $base);
        if (($data['editmode'] ?? 'advanced') === 'guided') {
            [$config, $captures] = self::identify($config, $data);
            if (isset($data['filtersjson'])) {
                $data = self::filter_rows($data);
            }
            $config['relationship']['filters'] = self::filter_definitions($data, $captures);
            $config = self::publication($config, $data);
            $config = \local_filebank\local\profile\guided_config::apply($config, $data);
            if (isset($data['filtersjson'])) {
                unset($config['relationship']['edition'], $config['migration']);
            }
        }
        $config['name'] = trim($data['profilename']);
        if ($config['name'] === '' || \core_text::strlen($config['name']) > 255) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        new \local_filebank\local\profile\profile_config($config);
        return $config;
    }
    /**
     * Decode.
     *
     * @param array $data Submitted fields.
     * @param array $base Server-loaded document.
     * @return array Result.
     */
    private static function decode(array $data, array $base): array {
        try {
            $config = self::initial_document($data, $base);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if (!is_array($config)) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        return $config;
    }
    /**
     * Initial document.
     *
     * @param array $data Submitted fields.
     * @param array $base Server-loaded document.
     * @return mixed Result.
     */
    private static function initial_document(array $data, array $base): mixed {
        if (($data['editmode'] ?? 'advanced') !== 'guided') {
            return json_decode($data['configjson'] ?? '', true, 64, JSON_THROW_ON_ERROR);
        }
        if ($base) {
            return $base;
        }
        if (trim($data['configjson'] ?? '') !== '') {
            return json_decode($data['configjson'], true, 64, JSON_THROW_ON_ERROR);
        }
        return ['schema_version' => 1, 'relationship' => ['parsers' => [[]]],
            'publication' => ['mode' => 'source-access-only', 'allowed_sources' => []]];
    }
    /**
     * Identify.
     *
     * @param array $config Initial document.
     * @param array $data Submitted course fields.
     * @return array Result.
     */
    private static function identify(array $config, array $data): array {
        if (count($config['relationship']['parsers']) !== 1) {
            throw new \moodle_exception('advancedprofilerequired', 'local_filebank');
        }
        $config['relationship']['parsers'][0]['field'] = $data['coursefield'];
        $config['relationship']['parsers'][0]['pattern'] = trim($data['coursepattern']);
        $config['relationship']['family_key'] = trim($data['familytemplate']);
        preg_match_all(
            '/\(\?(?:P?<([a-zA-Z][a-zA-Z0-9_]*)>|\x27([a-zA-Z][a-zA-Z0-9_]*)\x27)/',
            $config['relationship']['parsers'][0]['pattern'],
            $matches
        );
        $captures = array_filter(array_merge($matches[1], $matches[2]));
        $parser = &$config['relationship']['parsers'][0];
        $parser['required_captures'] = array_values(array_intersect($parser['required_captures'] ?? [], $captures));
        $parser['normalizers'] = array_intersect_key($parser['normalizers'] ?? [], array_flip($captures));
        preg_match_all('/\$\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $config['relationship']['display_label'] ?? '', $labels);
        if (array_diff($labels[1], $captures)) {
            $config['relationship']['display_label'] = $config['relationship']['family_key'];
        }

        return [$config, $captures];
    }
    /**
     * Filter rows.
     *
     * @param array $data Submitted editable filter rows.
     * @return array Result.
     */
    private static function filter_rows(array $data): array {
        $rows = \local_filebank\form\row_data::decode($data['filtersjson'], 9);
        foreach ($rows as $row) {
            if (
                array_diff(['capture', 'label', 'optional', 'key'], array_keys($row))
                || !is_bool($row['optional']) || !is_string($row['capture']) || !is_string($row['label'])
                || !is_string($row['key'])
            ) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        foreach (['capture', 'label', 'optional', 'key'] as $column) {
            $data['filter' . $column] = array_column($rows, $column);
        }

        return $data;
    }
    /**
     * Filter definitions.
     *
     * @param array $data Submitted filter values.
     * @param array $captures Available named captures.
     * @return array Result.
     */
    private static function filter_definitions(array $data, array $captures): array {
        $definitions = [];
        foreach ($data['filtercapture'] ?? [] as $index => $capture) {
            $label = trim($data['filterlabel'][$index] ?? '');
            if ($capture === '' && $label === '') {
                continue;
            }
            $key = ($data['filterkey'][$index] ?? '') ?: $capture;
            if (isset($definitions[$key]) || $label === '' || !in_array($capture, $captures, true)) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
            $definitions[$key] = ['capture' => $capture, 'label' => $label,
                'optional' => !empty($data['filteroptional'][$index])];
        }
        return $definitions;
    }
    /**
     * Publication.
     *
     * @param array $config Current document.
     * @param array $data Submitted public source options.
     * @return array Result.
     */
    private static function publication(array $config, array $data): array {
        if (!empty($data['sourcesdefined'])) {
            $controls = profile_form::source_controls();
            $unrepresented = array_diff($config['publication']['allowed_sources'] ?? [], $controls);
            $config['publication']['allowed_sources'] = array_values(
                array_merge(
                    $unrepresented,
                    array_filter($controls, static fn($key) => !empty($data['sources'][$key]), ARRAY_FILTER_USE_KEY)
                )
            );
        }
        return $config;
    }
}
