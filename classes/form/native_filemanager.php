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
 * Native filemanager and editor integration for activity forms.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\form;

use local_multipicker\local\selection\draft_scope;

/**
 * Preserve the native control and bind only supported material editing routes.
 */
class native_filemanager extends scoped_filemanager {
    /**
     * Register the native subclass through Moodle's public element factory.
     *
     * @param \core\hook\after_config $hook Bootstrap hook.
     */
    public static function register_native(\core\hook\after_config $hook): void {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        \MoodleQuickForm::registerElementType(
            'editor',
            $CFG->dirroot . '/local/multipicker/classes/form/native_editor.php',
            native_editor::class
        );
        \MoodleQuickForm::registerElementType(
            'filemanager',
            $CFG->dirroot . '/local/multipicker/classes/form/native_filemanager.php',
            self::class
        );
    }

    // phpcs:disable moodle.NamingConventions.ValidFunctionName
    /**
     * Render the native manager, then bind its actual populated draft and limits.
     *
     * @return string Native HTML.
     */
    public function toHtml() {
        global $PAGE, $DB, $CFG, $USER;
        $html = parent::toHtml();
        $basepath = (new \moodle_url($CFG->wwwroot))->get_path();
        $path = substr($PAGE->url->get_path(), strlen(rtrim($basepath, '/')));
        $content = $path === '/mod/folder/edit.php' && $this->getName() === 'files_filemanager';
        $settings = $path === '/course/modedit.php';
        if ((!$content && !$settings) || $this->isFrozen() || !isloggedin() || isguestuser()) {
            return $html;
        }
        if (!$DB->record_exists('local_multipicker', ['enabled' => 1])) {
            return $html;
        }
        try {
            \local_multipicker\local\selection\destination_access::require(
                (int)$PAGE->context->get_course_context()->instanceid,
                $PAGE->context,
                $this->getName()
            );
            // Core may allocate an empty draft without creating its root directory.
            get_file_storage()->create_directory(
                \context_user::instance($USER->id)->id,
                'user',
                'draft',
                (int)$this->getValue(),
                '/'
            );
            $token = draft_scope::find($PAGE->context, (int)$this->getValue(), sesskey());
            if (!$token) {
                $token = draft_scope::open($this, $PAGE->context);
            }
            draft_scope::assert_control($token, $this, $PAGE->context, sesskey());
        } catch (\moodle_exception $exception) {
            return $html;
        }
        return $html;
    }
    // phpcs:enable moodle.NamingConventions.ValidFunctionName
}
