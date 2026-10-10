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
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\local\profile;

/**
 * Resolves generic families and optional edition labels.
 */
final class profile_config {
    /**
     * @var array Configuration.
     */
    private array $config;

    /**
     * Validate configuration.
     *
     * @param array $config Declarative document.
     */
    public function __construct(array $config) {
        if (
            ($config['schema_version'] ?? null) !== 1 || !is_array($config['relationship'] ?? null)
                || empty($config['relationship']['parsers']) || !is_array($config['relationship']['parsers'])
                || empty($config['relationship']['family_key'])
        ) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        foreach (['scope', 'publication', 'migration', 'relationship'] as $name) {
            if (isset($config[$name]) && !is_array($config[$name])) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        if (
            count($config['relationship']['parsers']) > 32
                || !is_string($config['relationship']['family_key'])
        ) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        if (
            array_diff(
                array_keys($config),
                ['schema_version', 'status', 'name', 'scope', 'relationship', 'publication', 'migration']
            )
        ) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        foreach ($config['relationship']['parsers'] as $parser) {
            if (
                !is_array($parser) || !in_array($parser['field'] ?? '', ['idnumber', 'shortname', 'fullname'], true)
                    || !is_string($parser['pattern'] ?? null) || strlen($parser['pattern']) > 2048
            ) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
            foreach (['required_captures', 'normalizers'] as $key) {
                if (isset($parser[$key]) && !is_array($parser[$key])) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
            $this->matchpattern($parser['pattern'], '');
            foreach (($parser['normalizers'] ?? []) as $normalizers) {
                if (!is_array($normalizers) || array_diff($normalizers, ['trim', 'lowercase', 'uppercase'])) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
        }
        foreach (['edition', 'pair_dimensions', 'filters'] as $key) {
            if (isset($config['relationship'][$key]) && !is_array($config['relationship'][$key])) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        if (isset($config['publication']['allowed_sources']) && !is_array($config['publication']['allowed_sources'])) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        if (!in_array($config['relationship']['ambiguity_policy'] ?? 'block', ['block', 'first-match'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        foreach (
            ['relationship' => ['ambiguity_policy', 'parsers', 'family_key', 'display_label',
                'edition', 'pair_dimensions', 'filters'],
                'scope' => ['campus', 'category_ids'],
                'publication' => ['mode', 'allowed_sources', 'allow_hidden', 'allow_restricted']] as $name => $keys
        ) {
            if (array_diff(array_keys($config[$name] ?? []), $keys)) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        if (
            !in_array(
                $config['publication']['mode'] ?? 'source-access-only',
                ['source-access-only', 'explicit-family-publication'],
                true
            )
                || !empty($config['publication']['allow_hidden']) || !empty($config['publication']['allow_restricted'])
                || array_diff(
                    $config['publication']['allowed_sources'] ?? ['mod_folder/content', 'mod_resource/content'],
                    \local_multipicker\local\catalog\source_registry::keys()
                )
        ) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        $migration = $config['migration'] ?? [];
        foreach (
            ['missing_target' => ['block', 'create'], 'ambiguous_target' => ['block'],
                'same_path_same_content' => ['skip'], 'same_path_different_content' => ['block', 'overwrite'],
                'same_content_different_path' => ['skip-and-report', 'keep'],
                'on_item_failure' => ['continue-unaffected']] as $name => $allowed
        ) {
            if (isset($migration[$name]) && !in_array($migration[$name], $allowed, true)) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        foreach (['preserve_relative_paths', 'preserve_source', 'preserve_target_visibility'] as $name) {
            if (isset($migration[$name]) && $migration[$name] !== true) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        if (!in_array($migration['section_match']['strategy'] ?? 'ordinal', ['ordinal', 'name', 'mapping'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        $this->config = $config;
        foreach ($this->filter_definitions() as $name => $definition) {
            if (
                !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $name)
                    || !is_array($definition)
                    || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $definition['capture'] ?? '')
                    || !is_string($definition['label'] ?? null)
                    || $definition['label'] === '' || strlen($definition['label']) > 255
                    || (isset($definition['optional']) && !is_bool($definition['optional']))
                    || array_diff(array_keys($definition), ['capture', 'label', 'optional'])
            ) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        if (count($this->filter_definitions()) > 9) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
    }

    /**
     * Resolve a course.
     *
     * @param \stdClass $course Core record.
     * @return array|null Resolved identity.
     */
    public function resolve(\stdClass $course): ?array {
        $categories = $this->config['scope']['category_ids'] ?? [];
        if ($categories && !in_array((int)$course->category, array_map('intval', $categories), true)) {
            return null;
        }
        $resolved = [];
        foreach ($this->config['relationship']['parsers'] as $parser) {
            $captures = $this->matchpattern($parser['pattern'], (string)($course->{$parser['field']} ?? ''));
            if ($captures === null) {
                continue;
            }
            foreach (($parser['required_captures'] ?? []) as $name) {
                if (!isset($captures[$name]) || $captures[$name] === '') {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
            foreach (($parser['normalizers'] ?? []) as $name => $normalizers) {
                if (!isset($captures[$name])) {
                    continue;
                }
                foreach ($normalizers as $normalizer) {
                    $captures[$name] = match ($normalizer) {
                        'trim' => trim($captures[$name]),
                        'lowercase' => \core_text::strtolower($captures[$name]),
                        'uppercase' => \core_text::strtoupper($captures[$name]),
                    };
                }
            }
            $relationship = $this->config['relationship'];
            $family = self::template($relationship['family_key'], $captures);
            if ($family === '' || strlen($family) > 4096) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
            $edition = $captures[$relationship['edition']['capture'] ?? 'edition'] ?? null;
            if ($edition === '' || $edition === null) {
                $edition = null;
                if (isset($relationship['edition']) && empty($relationship['edition']['optional'])) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
            }
            $dimensions = [];
            foreach (($relationship['pair_dimensions'] ?? []) as $name) {
                $dimensions[$name] = $captures[$name] ?? '';
            }
            $filters = [];
            foreach ($this->filter_definitions() as $name => $definition) {
                $value = $captures[$definition['capture']] ?? '';
                if (($value === '' && empty($definition['optional'])) || strlen($value) > 255) {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
                $filters[$name] = $value;
            }
            $identity = ['familykey'  => $family, 'familyhash' => hash('sha256', $family),
                'edition' => $edition, 'dimensions' => $dimensions, 'filters' => $filters,
                'label' => isset($relationship['display_label'])
                    ? self::template($relationship['display_label'], $captures) : $family];
            $resolved[hash('sha256', json_encode($identity))] = $identity;
            if (($relationship['ambiguity_policy'] ?? 'block') === 'first-match') {
                return $identity;
            }
        }
        if (count($resolved) > 1) {
            throw new \moodle_exception('ambiguousprofile', 'local_multipicker');
        }
        return $resolved ? reset($resolved) : null;
    }

    /**
     * Match bounded PCRE.
     *
     * @param string $pattern Delimited pattern.
     * @param string $value Input.
     * @return array|null Named captures.
     */
    private function matchpattern(string $pattern, string $value): ?array {
        if (strlen($value) > 8192 || !in_array(substr($pattern, 0, 1), ['~', '#', '/'], true)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        $bounded = $pattern[0] . '(*LIMIT_MATCH=100000)(*LIMIT_DEPTH=1000)' . substr($pattern, 1);
        $result = @preg_match($bounded, $value, $matches, PREG_UNMATCHED_AS_NULL);
        if ($result === false) {
            throw new \moodle_exception('regexfailed', 'local_multipicker');
        }
        return $result ? array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY) : null;
    }

    /**
     * Expand capture placeholders.
     *
     * @param string $template Template.
     * @param array $captures Captures.
     * @return string Expanded text.
     */
    public static function template(string $template, array $captures): string {
        return preg_replace_callback('/\$\{([a-zA-Z][a-zA-Z0-9_]*)\}/', static function ($match) use ($captures) {
            if (!isset($captures[$match[1]])) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
            return (string)$captures[$match[1]];
        }, $template);
    }

    /**
     * Get configurable capture filters, including the legacy edition definition.
     *
     * @return array Stable keys and capture/label definitions.
     */
    public function filter_definitions(): array {
        $relationship = $this->config['relationship'];
        if (isset($relationship['filters'])) {
            if (!is_array($relationship['filters'])) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
            return $relationship['filters'];
        }
        return isset($relationship['edition']) ? ['edition' => [
            'capture' => $relationship['edition']['capture'] ?? 'edition',
            'label' => get_string('editionfilter', 'local_multipicker'),
            'optional' => !empty($relationship['edition']['optional']),
        ]] : [];
    }

    /**
     * Get validated configuration.
     *
     * @return array Document.
     */
    public function get_config(): array {
        return $this->config;
    }
}
