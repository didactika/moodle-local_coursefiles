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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\form;

use local_multipicker\local\selection\draft_scope;
use local_multipicker\local\selection\destination_access;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/form/editor.php');

/**
 * Bind activity editors to their existing native draft and editor instance.
 */
class native_editor extends \MoodleQuickForm_editor {
    // phpcs:disable moodle.NamingConventions.ValidFunctionName
    /**
     * Render the unchanged native editor before registering its real file contract.
     *
     * @return string Native editor HTML.
     */
    public function toHtml() {
        global $PAGE, $DB, $USER, $CFG;
        $html = parent::toHtml();
        $context = $this->_options['context'];
        if (
            $this->isFrozen() || !isloggedin() || isguestuser() || !$this->getMaxfiles()
                || !($this->_options['return_types'] & FILE_INTERNAL)
                || !(editors_get_preferred_editor($this->_values['format']) instanceof \editor_tiny\editor)
                || !$DB->record_exists('local_multipicker', ['enabled' => 1])
        ) {
            return $html;
        }
        try {
            destination_access::require((int)$context->get_course_context()->instanceid, $context, $this->getName());
            $draftid = (int)$this->_values['itemid'];
            get_file_storage()->create_directory(\context_user::instance($USER->id)->id, 'user', 'draft', $draftid, '/');
            if (!draft_scope::find($context, $draftid, sesskey())) {
                $course = get_course($context->get_course_context()->instanceid);
                draft_scope::open_contract([
                    'field' => $this->getName(), 'draftid' => $draftid,
                    'module' => $context->contextlevel === CONTEXT_COURSE ? ($PAGE->url->get_param('add') ?? '') : '',
                    'editorid' => $this->getAttribute('id'), 'environment' => 'editor',
                    'limits' => ['maxfiles' => (int)$this->getMaxfiles(),
                        'maxbytes' => get_user_max_upload_file_size(
                            $context,
                            $CFG->maxbytes,
                            $course->maxbytes,
                            (int)$this->getMaxbytes()
                        ),
                        'areamaxbytes' => (int)$this->getAreamaxbytes(), 'subdirs' => (bool)$this->getSubdirs(),
                        'accepted_types' => ['*']],
                ], $context);
            }
        } catch (\moodle_exception $exception) {
            return $html;
        }
        return $html;
    }
    // phpcs:enable moodle.NamingConventions.ValidFunctionName
}
