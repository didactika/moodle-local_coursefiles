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
 * FileBank native material import integration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\fixture;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Reads a real native form through the Moodle form subclass contract.
 */
abstract class native_form extends \moodleform {
    /**
     * Return the actual field without reconstructing or trusting its browser options.
     *
     * @param \moodleform $form Native owning form.
     * @param string $name Native field.
     * @return \MoodleQuickForm_filemanager|null Live native filemanager.
     */
    public static function control(\moodleform $form, string $name): ?\MoodleQuickForm_filemanager {
        if (!$form->_form->elementExists($name)) {
            return null;
        }
        $element = $form->_form->getElement($name);
        return $element instanceof \MoodleQuickForm_filemanager ? $element : null;
    }
}
