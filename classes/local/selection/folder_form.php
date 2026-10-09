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
 * Native folder editing form batch selection.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursebridge\local\selection;

use local_coursebridge\output\display;
use local_coursebridge\output\view;

/**
 * Course-module callbacks keep the real folder's filemanager and its normal save/cancel lifecycle.
 */
final class folder_form {
    /**
     * Add native selection controls beside the folder content settings.
     *
     * @param \moodleform_mod $wrapper Actual module form.
     * @param \MoodleQuickForm $mform Actual QuickForm.
     */
    public static function define(\moodleform_mod $wrapper, \MoodleQuickForm $mform): void {
        global $DB;
        if (!self::eligible($wrapper, $mform) || $mform->elementExists('cbheader')) {
            return;
        }
        $profiles = $DB->get_records_menu('local_coursebridge', ['enabled' => 1], 'name', 'id, name');
        if (!$profiles) {
            return;
        }
        self::add($mform, 'header', 'cbheader', get_string('batchheading', 'local_coursebridge'));
        self::add($mform, 'static', 'cbintro', '', get_string('batchintro', 'local_coursebridge'));
        self::add($mform, 'select', 'cbprofile', get_string('profiles', 'local_coursebridge'), $profiles);
        $mform->setDefault('cbprofile', array_key_first($profiles));
        $mform->setType('cbprofile', PARAM_INT);
        foreach (['q' => 'filenamefilter', 'edition' => 'editionfilter', 'shortname' => 'shortnamefilter'] as $key => $label) {
            self::add($mform, 'text', 'cb' . $key, get_string($label, 'local_coursebridge'), ['size' => 30]);
            $mform->setType('cb' . $key, PARAM_TEXT);
        }
        self::add($mform, 'select', 'cbmodule', get_string('modulefilter', 'local_coursebridge'), [
            '' => get_string('allmodules', 'local_coursebridge'), 'folder' => get_string('modulename', 'folder'),
            'resource' => get_string('modulename', 'resource'),
        ]);
        self::add($mform, 'select', 'cbmimetype', get_string('filetypefilter', 'local_coursebridge'), display::filetypes());
        foreach (['cbmodule', 'cbmimetype'] as $name) {
            $mform->setType($name, PARAM_TEXT);
        }
        self::add($mform, 'submit', 'cbsearch', get_string('batchsearch', 'local_coursebridge'));
        self::add(
            $mform,
            'autocomplete',
            'cbitems',
            get_string('batchselection', 'local_coursebridge'),
            [],
            ['multiple' => true, 'tags' => false]
        );
        $mform->setType('cbitems', PARAM_RAW);
        self::add($mform, 'static', 'cbselectionhelp', '', get_string('batchselectionhelp', 'local_coursebridge'));
        self::add($mform, 'submit', 'cbnext', get_string('batchnext', 'local_coursebridge'));
        self::add($mform, 'submit', 'cbreview', get_string('batchreview', 'local_coursebridge'));
        self::add($mform, 'static', 'cbreport', '', '');
        $mform->getElement('cbreport')->updateAttributes(['class' => 'w-100']);
        self::add($mform, 'submit', 'cbapply', get_string('batchapply', 'local_coursebridge'));
        foreach (['cbsearch', 'cbnext', 'cbreview', 'cbapply'] as $button) {
            $mform->registerNoSubmitButton($button);
        }
        foreach (['cbscope', 'cbdigest', 'cbafter', 'cbnextafter'] as $name) {
            self::add($mform, 'hidden', $name, '');
            $mform->setType($name, PARAM_ALPHANUM);
        }
        $mform->setExpanded('cbheader', false);
    }

    /**
     * Handle native no-submit actions after Moodle has populated submitted form values.
     *
     * @param \moodleform_mod $wrapper Actual module form.
     * @param \MoodleQuickForm $mform Actual QuickForm.
     */
    public static function after_data(\moodleform_mod $wrapper, \MoodleQuickForm $mform): void {
        global $OUTPUT;
        if (!$mform->elementExists('cbheader') || !self::eligible($wrapper, $mform)) {
            return;
        }
        $values = $mform->exportValues();
        $profileid = (int)($values['cbprofile'] ?? 0);
        $action = '';
        foreach (['cbsearch', 'cbnext', 'cbreview', 'cbapply'] as $button) {
            if ($wrapper->is_submitted() && !empty($mform->getSubmitValue($button))) {
                if ($action !== '') {
                    throw new \invalid_parameter_exception('Several CourseBridge actions');
                }
                $action = $button;
            }
        }
        if ($action !== '') {
            require_sesskey();
            $mform->setExpanded('cbheader', true);
        }
        $control = $mform->getElement('files');
        $context = $wrapper->get_context();
        $token = (string)($values['cbscope'] ?? '');
        $selected = (array)($mform->getSubmitValue('cbitems') ?? []);
        $selected = array_values(array_filter($selected, static fn($value) => $value !== '_qf__force_multiselect_submission'));
        $after = $action === 'cbsearch' ? '' : (string)($values['cbafter'] ?? '');
        if ($action === 'cbnext') {
            $after = (string)($values['cbnextafter'] ?? '');
        }
        $digest = '';
        $report = '';
        try {
            if ($token !== '') {
                draft_scope::assert_control($token, $control, $context, sesskey());
            } else {
                $token = draft_scope::open($control, $context);
            }
            $items = selection_options::parse($selected);
            if ($action === 'cbreview') {
                $review = draft_scope::review($token, $profileid, $items, sesskey());
                $digest = $review['blocked'] ? '' : $review['digest'];
                $report = self::report($review);
            } else if ($action === 'cbapply') {
                $approved = (string)($values['cbdigest'] ?? '');
                draft_scope::assert_review($token, $profileid, $items, $approved, sesskey());
                $review = draft_scope::apply($token, $approved, sesskey());
                $selected = [];
                $mform->setConstant('cbitems', []);
                $report = $OUTPUT->notification(get_string('batchadded', 'local_coursebridge'), 'success')
                    . self::report($review);
            }
        } catch (\moodle_exception $exception) {
            $report = $OUTPUT->notification(display::reason($exception->errorcode), 'error');
            if ($exception->errorcode === 'formscopeunavailable') {
                $token = draft_scope::open($control, $context);
            }
        }
        try {
            $filters = [];
            foreach (['q', 'edition', 'shortname', 'module', 'mimetype'] as $key) {
                $filters[$key] = (string)($values['cb' . $key] ?? '');
            }
            $page = selection_options::page(
                $wrapper->get_course()->id,
                $profileid,
                (int)($wrapper->get_coursemodule()->id ?? 0),
                $filters,
                $after,
                $selected
            );
            $mform->getElement('cbitems')->loadArray(array_map('s', $page['choices']), $selected);
            if ($page['limited']) {
                $mform->getElement('cbselectionhelp')->setValue(get_string('batchselectionhelp', 'local_coursebridge')
                    . $OUTPUT->notification(get_string('batchlimited', 'local_coursebridge'), 'info'));
            }
            if (!$page['choices']) {
                $report .= $OUTPUT->notification(get_string('batchempty', 'local_coursebridge'), 'info');
            }
            $mform->setConstant('cbnextafter', $page['next']);
            if (!$page['next']) {
                $mform->getElement('cbnext')->updateAttributes(['disabled' => 'disabled']);
            }
        } catch (\moodle_exception $exception) {
            $report .= $OUTPUT->notification(display::reason($exception->errorcode), 'error');
        }
        $mform->setConstant('cbscope', $token);
        $mform->setConstant('cbdigest', $digest);
        $mform->setConstant('cbafter', $after);
        $mform->getElement('cbreport')->setValue($report);
        if ($digest === '') {
            $mform->getElement('cbapply')->updateAttributes(['disabled' => 'disabled']);
        }
    }

    /**
     * Restrict automatic integration to authorized real folder forms with a writable native field.
     *
     * @param \moodleform_mod $wrapper Module form.
     * @param \MoodleQuickForm $mform QuickForm.
     * @return bool Eligible.
     */
    private static function eligible(\moodleform_mod $wrapper, \MoodleQuickForm $mform): bool {
        return $wrapper instanceof \mod_folder_mod_form && $mform->elementExists('files')
            && $mform->getElement('files') instanceof \MoodleQuickForm_filemanager
            && !$mform->getElement('files')->isFrozen()
            && has_capability('local/coursebridge:browse', \context_course::instance($wrapper->get_course()->id))
            && has_capability('local/coursebridge:reuse', \context_course::instance($wrapper->get_course()->id))
            && has_capability('moodle/course:manageactivities', $wrapper->get_context());
    }

    /**
     * Insert one real Moodle element before the common activity settings.
     *
     * @param \MoodleQuickForm $mform QuickForm.
     * @param mixed ...$arguments Native createElement arguments.
     */
    private static function add(\MoodleQuickForm $mform, ...$arguments): void {
        $element = $mform->createElement(...$arguments);
        $mform->insertElementBefore($element, 'modstandardelshdr');
    }

    /**
     * Render escaped review details with a theme-overridable component template.
     *
     * @param array $review Reviewed decisions.
     * @return string Native template output.
     */
    private static function report(array $review): string {
        global $PAGE;
        $rows = [];
        foreach ($review['operations'] as $operation) {
            $rows[] = ['filename' => $operation['filename'], 'filepath' => $operation['filepath'],
                'author' => $operation['snapshot']['author'], 'license' => $operation['snapshot']['license'],
                'decision' => display::outcome($operation['decision'])];
        }
        return $PAGE->get_renderer('local_coursebridge')->render(new view('selection_review', [
            'count' => $review['filecount'], 'size' => display_size($review['bytes']), 'rows' => $rows,
        ]));
    }
}
