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
        profile_schema::check($config);
        profile_policies::check($config);
        $this->config = $config;
        profile_policies::filters($this->filter_definitions());
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
            $captures = profile_pattern::match($parser['pattern'], (string)($course->{$parser['field']} ?? ''));
            if ($captures === null) {
                continue;
            }
            $this->required_captures($parser, $captures);
            $captures = $this->normalize_captures($parser, $captures);
            $identity = $this->identity($captures);
            $resolved[hash('sha256', json_encode($identity))] = $identity;
            if (($this->config['relationship']['ambiguity_policy'] ?? 'block') === 'first-match') {
                return $identity;
            }
        }
        if (count($resolved) > 1) {
            throw new \moodle_exception('ambiguousprofile', 'local_filebank');
        }
        return $resolved ? reset($resolved) : null;
    }

    /**
     * Expand capture placeholders.
     *
     * @param string $template Template.
     * @param array $captures Captures.
     * @return string Expanded text.
     */
    public static function template(string $template, array $captures): string {
        return preg_replace_callback(
            '/\$\{([a-zA-Z][a-zA-Z0-9_]*)\}/',
            static function ($match) use ($captures) {
                if (!isset($captures[$match[1]])) {
                    throw new \moodle_exception('invalidprofile', 'local_filebank');
                }
                return (string)$captures[$match[1]];
            },
            $template
        );
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
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
            return $relationship['filters'];
        }
        return isset($relationship['edition']) ? ['edition' => [
            'capture' => $relationship['edition']['capture'] ?? 'edition',
            'label' => get_string('editionfilter', 'local_filebank'),
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

    /**
     * Required captures.
     *
     * @param array $parser Validated course parser.
     * @param array $captures Named captures.
     */
    private function required_captures(array $parser, array $captures): void {
        foreach (($parser['required_captures'] ?? []) as $name) {
            if (!isset($captures[$name]) || $captures[$name] === '') {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
    }

    /**
     * Normalize captures.
     *
     * @param array $parser Validated course parser.
     * @param array $captures Named captures.
     * @return array Result.
     */
    private function normalize_captures(array $parser, array $captures): array {
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
        return $captures;
    }

    /**
     * Identity.
     *
     * @param array $captures Normalized named captures.
     * @return array Result.
     */
    private function identity(array $captures): array {
        $relationship = $this->config['relationship'];
        $family = self::template($relationship['family_key'], $captures);
        if ($family === '' || strlen($family) > 4096) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        $edition = $this->edition($relationship, $captures);
        $dimensions = [];
        foreach (($relationship['pair_dimensions'] ?? []) as $name) {
            $dimensions[$name] = $captures[$name] ?? '';
        }
        $filters = $this->capture_filters($captures);
        $identity = ['familykey'  => $family, 'familyhash' => hash('sha256', $family),
            'edition' => $edition, 'dimensions' => $dimensions, 'filters' => $filters,
            'label' => isset($relationship['display_label'])
                ? self::template($relationship['display_label'], $captures) : $family];
        return $identity;
    }

    /**
     * Edition.
     *
     * @param array $relationship Validated relationship rules.
     * @param array $captures Normalized named captures.
     * @return string|null Result.
     */
    private function edition(array $relationship, array $captures): ?string {
        $edition = $captures[$relationship['edition']['capture'] ?? 'edition'] ?? null;
        if ($edition === '' || $edition === null) {
            $edition = null;
            if (isset($relationship['edition']) && empty($relationship['edition']['optional'])) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        return $edition;
    }

    /**
     * Capture filters.
     *
     * @param array $captures Normalized named captures.
     * @return array Result.
     */
    private function capture_filters(array $captures): array {
        $filters = [];
        foreach ($this->filter_definitions() as $name => $definition) {
            $value = $captures[$definition['capture']] ?? '';
            if (($value === '' && empty($definition['optional'])) || strlen($value) > 255) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
            $filters[$name] = $value;
        }
        return $filters;
    }
}
