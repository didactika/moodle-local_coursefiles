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
 * Native configuration upload with complete validation.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Uploads portable profiles without overwriting existing records.
 */
final class profile_import_form extends \moodleform {
    /**
     * Define the native single-document upload.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement(
            'filepicker',
            'bundle',
            get_string('file'),
            null,
            ['accepted_types' => ['.json'], 'maxbytes' => \local_multipicker\local\profile\profile_bundle::MAX_BYTES]
        );
        $form->addRule('bundle', null, 'required');
        $form->addHelpButton('bundle', 'bundle', 'local_multipicker');
        $this->add_action_buttons(true, get_string('importprofiles', 'local_multipicker'));
    }

    /**
     * Refuse invalid documents before showing a successful submission.
     *
     * @param array $data Submitted data.
     * @param array $files Native files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        try {
            \local_multipicker\local\profile\profile_bundle::documents(self::uploaded_document((int)($data['bundle'] ?? 0)));
        } catch (\moodle_exception $exception) {
            $errors['bundle'] = $exception->getMessage();
        }
        return $errors;
    }

    /**
     * Read this actor's single bounded draft during validation, before get_file_content is available.
     *
     * @param int $draftid Native submitted draft.
     * @return string Uploaded document.
     */
    public static function uploaded_document(int $draftid): string {
        global $USER;
        require_capability('local/multipicker:manageprofiles', \context_system::instance());
        $files = $draftid > 0 ? get_file_storage()->get_area_files(
            \context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftid,
            'id',
            false
        ) : [];
        if (count($files) !== 1) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        $file = reset($files);
        if (
            $file->get_filesize() > \local_multipicker\local\profile\profile_bundle::MAX_BYTES
                || strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) !== 'json'
        ) {
            throw new \moodle_exception('invalidprofilebundle', 'local_multipicker');
        }
        return $file->get_content();
    }
}
