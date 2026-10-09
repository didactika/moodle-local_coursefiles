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
 * Site policy form.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Edits the existing policy keys through native Moodle controls.
 */
final class settings_form extends \moodleform {
    /**
     * Define supported values and defaults.
     *
     * @return array Setting choices.
     */
    public static function choices(): array {
        return [
            'author_policy' => ['preserve_locked', 'editable_with_provenance'],
            'author_course_override' => ['disabled', 'tighten_only'],
            'author_missing_policy' => ['preserve_unknown', 'block_reuse'],
        ];
    }

    /**
     * Define grouped policy settings.
     */
    public function definition(): void {
        $form = $this->_form;
        foreach (self::choices() as $name => $values) {
            $options = [];
            foreach ($values as $value) {
                $options[$value] = get_string($value, 'local_coursebridge');
            }
            $form->addElement('select', $name, get_string($name, 'local_coursebridge'), $options);
            $form->addElement('static', $name . '_help', '', get_string($name . '_desc', 'local_coursebridge'));
            $form->setDefault($name, $values[0]);
        }
        $this->add_action_buttons(false);
    }

    /**
     * Reject unsupported values before persistence.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        foreach (self::choices() as $name => $values) {
            if (!in_array($data[$name] ?? '', $values, true)) {
                $errors[$name] = get_string('invalidpolicy', 'local_coursebridge');
            }
        }
        return $errors;
    }
}
