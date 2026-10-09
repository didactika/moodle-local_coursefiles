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
 * Native filemanager integration for folder and resource forms.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\form;

use local_coursebridge\local\selection\draft_scope;

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
            'filemanager',
            $CFG->dirroot . '/local/coursebridge/classes/form/native_filemanager.php',
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
        $fresh = empty($this->getValue());
        $html = parent::toHtml();
        $basepath = (new \moodle_url($CFG->wwwroot))->get_path();
        $path = substr($PAGE->url->get_path(), strlen(rtrim($basepath, '/')));
        $content = $path === '/mod/folder/edit.php' && $this->getName() === 'files_filemanager';
        $settings = $path === '/course/modedit.php' && $this->getName() === 'files';
        if ((!$content && !$settings) || $this->isFrozen() || !isloggedin() || isguestuser()) {
            return $html;
        }
        $module = $PAGE->cm->modname ?? $PAGE->url->get_param('add');
        if (
            !in_array($module, ['folder', 'resource'], true)
                || !$DB->record_exists('local_coursebridge', ['enabled' => 1])
        ) {
            return $html;
        }
        try {
            if ($fresh) {
                \local_coursebridge\local\selection\destination_access::require(
                    (int)$PAGE->context->get_course_context()->instanceid,
                    $PAGE->context,
                    $this->getName()
                );
                get_file_storage()->create_directory(
                    \context_user::instance($USER->id)->id,
                    'user',
                    'draft',
                    (int)$this->getValue(),
                    '/'
                );
            }
            $token = draft_scope::find($PAGE->context, (int)$this->getValue(), sesskey());
            if (!$token) {
                $token = draft_scope::open($this, $PAGE->context);
            }
            draft_scope::assert_control($token, $this, $PAGE->context, sesskey());
            $repositoryid = $DB->get_field_sql(
                'SELECT MIN(i.id) FROM {repository_instances} i
                JOIN {repository} r ON r.id = i.typeid WHERE r.type = :type AND r.visible = 1',
                ['type' => 'coursebridge']
            );
            if (
                $repositoryid && has_capability('repository/coursebridge:view', $PAGE->context)
                    && has_capability('local/coursebridge:browse', $PAGE->context->get_course_context())
            ) {
                $PAGE->requires->js_call_amd('local_coursebridge/browser', 'attach', [
                    $this->getAttribute('id'), (int)$repositoryid, get_string('importmaterials', 'local_coursebridge'),
                ]);
            }
        } catch (\moodle_exception $exception) {
            return $html;
        }
        return $html;
    }
    // phpcs:enable moodle.NamingConventions.ValidFunctionName
}
