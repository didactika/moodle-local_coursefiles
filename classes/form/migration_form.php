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
 * Capture scope and layout selection before preview.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Uses an explicit selected profile and configured capture dimensions.
 */
final class migration_form extends \moodleform {
    /**
     * Define the selected migration scope.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement('hidden', 'profileid', $this->_customdata['profileid']);
        $form->setType('profileid', PARAM_INT);
        $form->addElement('hidden', 'step', 'scope');
        $form->setType('step', PARAM_ALPHA);
        $form->addElement(
            'select',
            'scopekey',
            get_string('migrationdimension', 'local_multipicker'),
            $this->_customdata['dimensions'] ?? ['edition' => get_string('edition', 'local_multipicker')]
        );
        foreach (['sourceedition', 'targetedition'] as $name) {
            $side = $name === 'sourceedition' ? 'source' : 'target';
            $form->addElement('header', $side . 'scope', get_string($side . 'courses', 'local_multipicker'));
            $form->setExpanded($side . 'scope', true);
            $form->addElement('text', $name, get_string($side . 'value', 'local_multipicker'), ['list' => 'cb-editions']);
            $form->setType($name, PARAM_TEXT);
            $form->addRule($name, null, 'required');
        }
        $options = '';
        foreach ($this->_customdata['editions'] as $edition) {
            $options .= \html_writer::tag('option', '', ['value' => $edition]);
        }
        $form->addElement('html', \html_writer::tag('datalist', $options, ['id' => 'cb-editions']));
        $form->addElement('header', 'placementreview', get_string('migrationrules', 'local_multipicker'));
        $form->setExpanded('placementreview', true);
        if ($this->_customdata['ordinal']) {
            $form->addElement('advcheckbox', 'layoutconfirmed', get_string('layoutconfirmed', 'local_multipicker'));
            $form->addElement('static', 'layouthelp', '', get_string('layouthelp', 'local_multipicker'));
        }
        $this->add_action_buttons(false, get_string('createpreview', 'local_multipicker'));
    }

    /**
     * Explain common scope errors before calling the planner.
     *
     * @param array $data Submitted scope.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if ($data['sourceedition'] === $data['targetedition']) {
            $errors['targetedition'] = get_string('differenteditions', 'local_multipicker');
        }
        if ($this->_customdata['ordinal'] && empty($data['layoutconfirmed'])) {
            $errors['layoutconfirmed'] = get_string('layoutrequired', 'local_multipicker');
        }
        return $errors;
    }
}
