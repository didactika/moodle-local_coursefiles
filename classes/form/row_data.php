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

use local_filebank\local\compatibility;

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
        global $PAGE, $OUTPUT;
        foreach ($columns as &$column) {
            $column['help'] = $OUTPUT->help_icon('rowfield_' . $column['key'], 'local_filebank');
        }
        unset($column);
        $form->getElement($name)->updateAttributes([
            'data-filebank-schema' => json_encode(['columns' => $columns, 'maximum' => $maximum], JSON_UNESCAPED_UNICODE),
        ]);
        $PAGE->requires->js_call_amd('local_filebank/row_editor', 'init', ['id_' . $name]);
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
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        if (!is_array($rows) || !compatibility::is_list($rows) || count($rows) > $maximum || strlen($json) > 131072) {
            throw new \moodle_exception('invalidprofile', 'local_filebank');
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new \moodle_exception('invalidprofile', 'local_filebank');
            }
        }
        return $rows;
    }
}
