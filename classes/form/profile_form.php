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
 * Profile editor with validated unsaved previews.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursebridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Edits a named profile while keeping arbitrary supported rules available.
 */
final class profile_form extends \moodleform {
    /**
     * Define fields and distinct preview/save actions.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement('hidden', 'profileid', $this->_customdata['profileid'] ?? 0);
        $form->setType('profileid', PARAM_INT);
        $form->addElement('text', 'profilename', get_string('name', 'core'), ['maxlength' => 255]);
        $form->setType('profilename', PARAM_TEXT);
        $form->addRule('profilename', null, 'required');
        $form->addElement('header', 'rules', get_string('relationshiprules', 'local_coursebridge'));
        $form->setExpanded('rules');
        $form->addElement(
            'textarea',
            'configjson',
            get_string('profilejson', 'local_coursebridge'),
            ['rows' => 14, 'class' => 'w-100']
        );
        $form->setType('configjson', PARAM_RAW);
        $form->addRule('configjson', null, 'required');
        $buttons = [];
        $buttons[] = $form->createElement('submit', 'saveprofile', get_string('saveprofile', 'local_coursebridge'));
        $buttons[] = $form->createElement('submit', 'previewrules', get_string('previewrules', 'local_coursebridge'));
        $buttons[] = $form->createElement('cancel');
        $form->addGroup($buttons, 'buttons', '', [' '], false);
        $form->closeHeaderBefore('buttons');
    }

    /**
     * Parse and validate the complete document before saving or previewing.
     *
     * @param array $data Submitted fields.
     * @return array Validated configuration.
     */
    public static function document(array $data): array {
        try {
            $config = json_decode($data['configjson'], true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_coursebridge');
        }
        if (!is_array($config)) {
            throw new \moodle_exception('invalidprofile', 'local_coursebridge');
        }
        $config['name'] = trim($data['profilename']);
        if ($config['name'] === '' || \core_text::strlen($config['name']) > 255) {
            throw new \moodle_exception('invalidprofile', 'local_coursebridge');
        }
        new \local_coursebridge\local\profile\profile_config($config);
        return $config;
    }

    /**
     * Show configuration errors beside the editor without writing a revision.
     *
     * @param array $data Submitted fields.
     * @param array $files Submitted files.
     * @return array Field errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        try {
            self::document($data);
        } catch (\moodle_exception $exception) {
            $errors['configjson'] = $exception->getMessage();
        }
        return $errors;
    }
}
