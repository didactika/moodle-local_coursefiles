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
 * Declarative profile administration form.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Validates and activates a JSON profile.
 */
final class profile_form extends \moodleform {
    /**
     * Define editable fields.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement('hidden', 'profileid', $this->_customdata['profileid'] ?? 0);
        $form->setType('profileid', PARAM_INT);
        $form->addElement(
            'textarea',
            'configjson',
            get_string('profilejson', 'local_coursebridge'),
            ['rows' => 24, 'cols' => 100]
        );
        $form->setType('configjson', PARAM_RAW);
        $form->addRule('configjson', null, 'required');
        $this->add_action_buttons(false, get_string('saveprofile', 'local_coursebridge'));
    }
}
