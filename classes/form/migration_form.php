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
 * Edition and placement review form.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Selects exact editions before any write.
 */
final class migration_form extends \moodleform {
    /**
     * Define migration scope.
     */
    public function definition(): void {
        global $DB;
        $form = $this->_form;
        $options = [];
        foreach ($DB->get_records('local_coursebridge', ['enabled' => 1], 'name') as $profile) {
            $options[$profile->id] = $profile->name;
        }
        $form->addElement('select', 'profileid', get_string('profile', 'local_coursebridge'), $options);
        foreach (['sourceedition', 'targetedition'] as $name) {
            $form->addElement('text', $name, get_string($name, 'local_coursebridge'));
            $form->setType($name, PARAM_TEXT);
            $form->addRule($name, null, 'required');
        }
        $form->addElement('advcheckbox', 'layoutconfirmed', get_string('layoutconfirmed', 'local_coursebridge'));
        $this->add_action_buttons(false, get_string('preview', 'local_coursebridge'));
    }
}
