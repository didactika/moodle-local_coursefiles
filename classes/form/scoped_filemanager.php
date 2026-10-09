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
 * Native form control with a trusted batch contract.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\form;

use local_coursebridge\local\selection\draft_scope;

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
            'coursebridgefilemanager',
            $CFG->dirroot . '/local/coursebridge/classes/form/scoped_filemanager.php',
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
        global $CFG;
        if ($this->_flagFrozen || !$this->getName() || !($this->_options['return_types'] & FILE_INTERNAL)) {
            throw new \moodle_exception('formscopeunavailable', 'local_coursebridge');
        }
        [, $course] = get_context_info_array($context->id);
        $maxbytes = max(0, (int)$this->getMaxbytes());
        $types = (new \core_form\filetypes_util())->normalize_file_types($this->_options['accepted_types']);
        return ['field' => $this->getName(), 'draftid' => (int)$this->getValue(), 'limits' => [
            'maxfiles' => (int)$this->getMaxfiles(),
            'maxbytes' => get_user_max_upload_file_size($context, $CFG->maxbytes, $course->maxbytes, $maxbytes),
            'areamaxbytes' => (int)$this->getAreamaxbytes(), 'subdirs' => (bool)$this->getSubdirs(),
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
