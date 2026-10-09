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
 * Shared native presentation labels and options.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\output;

/**
 * Provides plain localized choices and outcomes without reading business data.
 */
final class display {
    /**
     * Apply Moodle filters to a plain field label or name in its actual context.
     *
     * @param string $value Stored label.
     * @param \context|null $context Display context.
     * @return string Filtered safe HTML, suitable for a name slot (not an attribute).
     */
    public static function name(string $value, ?\context $context = null): string {
        global $PAGE;
        return format_string($value, true, ['context' => $context ?? $PAGE->context ?? \context_system::instance()]);
    }

    /**
     * Export select options.
     *
     * @param array $choices Value to label map.
     * @param string $selected Current value.
     * @return array Mustache options.
     */
    public static function options(array $choices, string $selected): array {
        $options = [];
        foreach ($choices as $value => $label) {
            $options[] = ['value' => (string)$value, 'label' => self::name($label), 'selected' => (string)$value === $selected];
        }
        return $options;
    }

    /**
     * Export common exact MIME choices.
     *
     * @return array MIME to readable label.
     */
    public static function filetypes(): array {
        return [
            '' => get_string('alltypes', 'local_multipicker'),
            'application/pdf' => 'PDF',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word (.docx)',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel (.xlsx)',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint (.pptx)',
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
            'video/mp4' => 'MP4',
            'application/zip' => 'ZIP',
            'text/plain' => get_string('plaintext', 'local_multipicker'),
        ];
    }

    /**
     * Translate a stored decision or state without showing internal JSON.
     *
     * @param string $value Stored code.
     * @return string User-facing label.
     */
    public static function outcome(string $value): string {
        $key = 'outcome_' . $value;
        return get_string_manager()->string_exists($key, 'local_multipicker')
            ? get_string($key, 'local_multipicker') : get_string('operationfailed', 'local_multipicker');
    }

    /**
     * Translate a safe plugin error outcome.
     *
     * @param string $value Error code.
     * @return string User-facing explanation.
     */
    public static function reason(string $value): string {
        return get_string_manager()->string_exists($value, 'local_multipicker')
            ? get_string($value, 'local_multipicker') : get_string('operationfailed', 'local_multipicker');
    }
}
