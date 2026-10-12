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
 * Native form control with a trusted batch contract.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank\form;

use local_filebank\local\selection\draft_scope;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/form/filemanager.php');

/**
 * Opt-in native element: form options originate from the owning server-side definition.
 */
class scoped_filemanager extends \MoodleQuickForm_filemanager {
    /**
     * Register the native subclass with Moodle's custom element API.
     */
    public static function register(): void {
        global $CFG;
        \MoodleQuickForm::registerElementType(
            'filebankfilemanager',
            $CFG->dirroot . '/local/filebank/classes/form/scoped_filemanager.php',
            self::class
        );
    }

    /**
     * Read the actual native control contract without accepting request-provided limits.
     *
     * @param \context $context Actual owning form context.
     * @return array Native field, draft and normalized control limits.
     */
    public function get_contract(\context $context): array {
        return self::read_contract($this, $context);
    }

    /**
     * Read an existing native control using the parent class's protected extension contract.
     *
     * @param \MoodleQuickForm_filemanager $element Existing native control.
     * @param \context $context Owning context.
     * @return array Server-defined native contract.
     */
    public static function read_contract(\MoodleQuickForm_filemanager $element, \context $context): array {
        global $CFG, $PAGE;
        if ($element->_flagFrozen || !$element->getName() || !($element->_options['return_types'] & FILE_INTERNAL)) {
            throw new \moodle_exception('formscopeunavailable', 'local_filebank');
        }
        [, $course] = get_context_info_array($context->id);
        $maxbytes = max(0, (int)$element->getMaxbytes());
        $types = (new \core_form\filetypes_util())->normalize_file_types($element->_options['accepted_types']);
        $module = $context->contextlevel === CONTEXT_COURSE && $element instanceof native_filemanager
            ? ($PAGE->url->get_param('add') ?? '') : '';
        return ['field' => $element->getName(), 'draftid' => (int)$element->getValue(), 'module' => $module, 'limits' => [
            'maxfiles' => (int)$element->getMaxfiles(),
            'maxbytes' => get_user_max_upload_file_size($context, $CFG->maxbytes, $course->maxbytes, $maxbytes),
            'areamaxbytes' => (int)$element->getAreamaxbytes(), 'subdirs' => (bool)$element->getSubdirs(),
            'accepted_types' => $types ?: ['*'],
        ]];
    }

    /**
     * Bind the live native control to a session-local batch scope.
     *
     * @param \context $context Owning form context.
     * @param string $basepath Existing destination directory.
     * @return string Opaque scope token.
     */
    public function open_scope(\context $context, string $basepath = '/'): string {
        return draft_scope::open($this, $context, $basepath);
    }
}
