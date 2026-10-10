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
 * Capture scope and layout selection before preview.
 *
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Configures capture filters and multiple folder correspondences for this migration only.
 */
final class migration_form extends \moodleform {
    /**
     * Define the reviewed migration scope and destination behavior.
     */
    public function definition(): void {
        global $PAGE, $OUTPUT;
        $form = $this->_form;
        $form->addElement('hidden', 'profileid', $this->_customdata['profileid']);
        $form->setType('profileid', PARAM_INT);
        $form->addElement('hidden', 'step', 'scope');
        $form->setType('step', PARAM_ALPHA);
        foreach (['source', 'target'] as $side) {
            if (!$this->_customdata['dimensions']) {
                continue;
            }
            $form->addElement('header', $side . 'scope', get_string($side . 'courses', 'local_multipicker'));
            $form->setExpanded($side . 'scope', true);
            foreach ($this->_customdata['dimensions'] as $key => $label) {
                $name = $side . 'filters[' . $key . ']';
                $form->addElement('text', $name, $label, ['maxlength' => 255]);
                $form->setType($name, PARAM_TEXT);
                $form->addHelpButton($name, 'migrationfilter', 'local_multipicker');
            }
        }
        $form->addElement('header', 'placementreview', get_string('migrationrules', 'local_multipicker'));
        $form->setExpanded('placementreview', true);
        $migration = $this->_customdata['migration'] ?? [];
        if (!$migration) {
            $migration = ['source_module' => ['field' => 'name', 'pattern' => '~^(?<name>.+)$~'],
                'target_module' => ['field' => 'name', 'template' => '${name}']];
        }
        $rules = $migration['correspondences'] ?? [$migration];
        $rows = [];
        foreach ($rules as $rule) {
            $row = [];
            foreach (['source', 'target'] as $side) {
                $predicate = $rule[$side . '_module'] ?? ['equals' => ''];
                $method = isset($predicate['pattern']) ? 'pattern' : (isset($predicate['template']) ? 'template' : 'equals');
                $row[$side . 'field'] = $predicate['field'] ?? 'name';
                $row[$side . 'method'] = $method;
                $row[$side . 'value'] = $predicate[$method];
            }
            $rows[] = $row;
        }
        $form->addElement(
            'textarea',
            'correspondencesjson',
            get_string('foldercorrespondences', 'local_multipicker'),
            ['rows' => 8, 'id' => 'id_correspondencesjson']
        );
        $form->setType('correspondencesjson', PARAM_RAW);
        $form->setDefault('correspondencesjson', json_encode($rows));
        $columns = [];
        foreach (['source', 'target'] as $side) {
            $columns[] = ['key' => $side . 'field', 'label' => get_string($side . 'folderfield', 'local_multipicker'),
                'type' => 'text', 'options' => [['value' => 'name', 'label' => get_string('name')],
                ['value' => 'idnumber', 'label' => get_string('idnumber')]]];
            $columns[] = ['key' => $side . 'method', 'label' => get_string('matchmethod', 'local_multipicker'),
                'type' => 'text', 'options' => [['value' => 'equals', 'label' => get_string('exactmatch', 'local_multipicker')],
                ['value' => 'pattern', 'label' => get_string('regexmatch', 'local_multipicker')],
                ['value' => 'template', 'label' => get_string('templatematch', 'local_multipicker')]]];
            $columns[] = ['key' => $side . 'value', 'label' => get_string($side . 'foldervalue', 'local_multipicker'),
                'type' => 'text', 'maxlength' => 2048];
        }
        row_data::enhance($form, 'correspondencesjson', $columns, 20);
        $choices = [
            'sectionstrategy' => ['ordinal' => 'section_ordinal', 'name' => 'section_name', 'mapping' => 'section_mapping'],
            'duplicatepolicy' => ['skip-and-report' => 'duplicateskip', 'keep' => 'duplicatekeep'],
            'missingtarget' => ['block' => 'missingblock', 'create' => 'missingcreate'],
            'conflictpolicy' => ['block' => 'conflictblock', 'overwrite' => 'conflictoverwrite'],
        ];
        foreach ($choices as $name => $values) {
            $labels = [];
            foreach ($values as $key => $label) {
                $labels[$key] = get_string($label, 'local_multipicker');
            }
            $form->addElement('select', $name, get_string($name, 'local_multipicker'), $labels);
        }
        $form->setDefault('sectionstrategy', $migration['section_match']['strategy'] ?? 'ordinal');
        $form->setDefault('duplicatepolicy', $migration['same_content_different_path'] ?? 'skip-and-report');
        $form->setDefault('missingtarget', $migration['missing_target'] ?? 'block');
        $form->setDefault('conflictpolicy', $migration['same_path_different_content'] ?? 'block');
        $form->addElement(
            'textarea',
            'sectionmapjson',
            get_string('section_mapping', 'local_multipicker'),
            ['rows' => 4, 'id' => 'id_sectionmapjson']
        );
        $form->setType('sectionmapjson', PARAM_RAW);
        $sectionrows = [];
        foreach ($migration['section_match']['map'] ?? [] as $source => $target) {
            $sectionrows[] = ['source' => (string)$source, 'target' => (string)$target];
        }
        $form->setDefault('sectionmapjson', json_encode($sectionrows));
        row_data::enhance($form, 'sectionmapjson', [
            ['key' => 'source', 'label' => get_string('sourcesectionid', 'local_multipicker'), 'type' => 'text'],
            ['key' => 'target', 'label' => get_string('targetsectionid', 'local_multipicker'), 'type' => 'text'],
        ], 200);
        $form->hideIf('sectionmapjson', 'sectionstrategy', 'neq', 'mapping');
        $form->addElement('advcheckbox', 'layoutconfirmed', get_string('layoutconfirmed', 'local_multipicker'));
        $form->hideIf('layoutconfirmed', 'sectionstrategy', 'neq', 'ordinal');
        $buttons = [
            $form->createElement('submit', 'submitbutton', get_string('createpreview', 'local_multipicker')),
        ];
        $form->addGroup($buttons, 'buttons', '', [' '], false);
        $form->closeHeaderBefore('buttons');
        foreach (
            ['correspondencesjson', 'sectionstrategy', 'duplicatepolicy', 'missingtarget',
            'conflictpolicy', 'sectionmapjson', 'layoutconfirmed'] as $name
        ) {
            $form->addHelpButton($name, $name, 'local_multipicker');
        }
    }

    /**
     * Validate the complete migration document before saving a review.
     *
     * @param array $data Native form values.
     * @param array $files Uploaded files.
     * @return array Field errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        try {
            \local_multipicker\local\migration\migration_rules::from_form($data);
        } catch (\moodle_exception $exception) {
            $errors['correspondencesjson'] = $exception->getMessage();
        }
        if (($data['sectionstrategy'] ?? '') === 'ordinal' && empty($data['layoutconfirmed'])) {
            $errors['layoutconfirmed'] = get_string('layoutrequired', 'local_multipicker');
        }
        if (
            $this->_customdata['dimensions'] && empty(array_filter($data['sourcefilters'] ?? []))
            && empty(array_filter($data['targetfilters'] ?? []))
        ) {
            $errors['correspondencesjson'] = get_string('migrationscoperequired', 'local_multipicker');
        }
        return $errors;
    }
}
