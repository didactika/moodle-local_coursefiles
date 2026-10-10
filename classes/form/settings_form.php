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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

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
                $options[$value] = get_string($value, 'local_multipicker');
            }
            $form->addElement('select', $name, get_string($name, 'local_multipicker'), $options);
            $form->addElement('static', $name . '_help', '', get_string($name . '_desc', 'local_multipicker'));
            $form->setDefault($name, $values[0]);
        }
        $form->addElement('header', 'retention', get_string('retention', 'local_multipicker'));
        foreach (
            [
            'logretentiondays' => 90, 'migrationretentiondays' => 365, 'versionretentiondays' => 0, 'versionretentionmax' => 0,
            ] as $name => $days
        ) {
            $form->addElement('text', $name, get_string($name, 'local_multipicker'), ['size' => 6]);
            $form->setType($name, PARAM_INT);
            $form->addRule($name, null, 'numeric', null, 'client');
            $form->setDefault($name, $days);
            if (str_starts_with($name, 'version')) {
                $form->addHelpButton($name, $name, 'local_multipicker');
            }
        }
        $form->addElement('static', 'versionretentionhelp', '', get_string('versionretentionhelp', 'local_multipicker'));
        $form->addElement('static', 'retentionhelp', '', get_string('retentionhelp', 'local_multipicker'));
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
                $errors[$name] = get_string('invalidpolicy', 'local_multipicker');
            }
        }
        foreach (['logretentiondays', 'migrationretentiondays', 'versionretentiondays', 'versionretentionmax'] as $name) {
            if (!isset($data[$name]) || $data[$name] < 0 || $data[$name] > 36500) {
                $errors[$name] = get_string('invalidretention', 'local_multipicker');
            }
        }
        return $errors;
    }
}
