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
 * Profile editor with validated unsaved previews.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

/**
 * Validates bounded row documents submitted by native forms.
 */
final class row_data {
    /**
     * Keep translated schemas on their native field, outside AMD call arguments.
     *
     * @param \MoodleQuickForm $form Native form.
     * @param string $name Field name.
     * @param array $columns Editable column contracts.
     * @param int $maximum Server-enforced row limit.
     */
    public static function enhance(\MoodleQuickForm $form, string $name, array $columns, int $maximum): void {
        global $PAGE;
        $form->getElement($name)->updateAttributes([
            'data-multipicker-schema' => json_encode(['columns' => $columns, 'maximum' => $maximum], JSON_UNESCAPED_UNICODE),
        ]);
        $PAGE->requires->js_call_amd('local_multipicker/row_editor', 'init', ['id_' . $name]);
    }

    /**
     * Decode a list without coercing malformed input.
     *
     * @param string $json Serialized form rows.
     * @param int $maximum Maximum rows.
     * @return array Rows.
     */
    public static function decode(string $json, int $maximum): array {
        try {
            $rows = json_decode($json, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        if (!is_array($rows) || !array_is_list($rows) || count($rows) > $maximum || strlen($json) > 131072) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new \moodle_exception('invalidprofile', 'local_multipicker');
            }
        }
        return $rows;
    }
}
