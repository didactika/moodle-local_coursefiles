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
 * Owning native form scope integration checks.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\fixture;

use local_filebank\form\scoped_filemanager;

/**
 * Owning native form used by integration checks.
 */
final class scoped_form extends \moodleform {
    /**
     * Create real form elements using the custom-element registration API.
     */
    public function definition(): void {
        scoped_filemanager::register();
        $this->_form->addElement('text', 'title', 'Title');
        $this->_form->setType('title', PARAM_TEXT);
        $this->_form->addElement('filebankfilemanager', 'files', 'Files', null, $this->_customdata);
        $this->_form->addElement('submit', 'refresh', 'Refresh draft');
        $this->_form->registerNoSubmitButton('refresh');
        $this->add_action_buttons();
    }

    /**
     * Access the owning control through the real QuickForm.
     *
     * @return scoped_filemanager Native control.
     */
    public function control(): scoped_filemanager {
        return $this->_form->getElement('files');
    }
}
