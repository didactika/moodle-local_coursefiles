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
 * Capture scope and layout selection before preview.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_filebank\form;

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
        $form = $this->_form;
        $this->scope_fields($form);
        $rows = $this->correspondence_rows($form);
        $this->correspondence_fields($form, $rows);
        $this->destination_fields($form);
        $this->action_fields($form);
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
            \local_filebank\local\migration\migration_rules::from_form($data);
        } catch (\moodle_exception $exception) {
            $errors['correspondencesjson'] = $exception->getMessage();
        }
        if (($data['sectionstrategy'] ?? '') === 'ordinal' && empty($data['layoutconfirmed'])) {
            $errors['layoutconfirmed'] = get_string('layoutrequired', 'local_filebank');
        }
        if (
            $this->_customdata['dimensions'] && empty(array_filter($data['sourcefilters'] ?? []))
            && empty(array_filter($data['targetfilters'] ?? []))
        ) {
            $errors['correspondencesjson'] = get_string('migrationscoperequired', 'local_filebank');
        }
        return $errors;
    }

    /**
     * Define the scope fields controls.
     *
     * @param \MoodleQuickForm $form Native form element factory.
     */
    private function scope_fields(\MoodleQuickForm $form): void {
        $form->addElement('hidden', 'profileid', $this->_customdata['profileid']);
        $form->setType('profileid', PARAM_INT);
        $form->addElement('hidden', 'step', 'scope');
        $form->setType('step', PARAM_ALPHA);
        foreach (['source', 'target'] as $side) {
            if (!$this->_customdata['dimensions']) {
                continue;
            }
            $form->addElement('header', $side . 'scope', get_string($side . 'courses', 'local_filebank'));
            $form->setExpanded($side . 'scope', true);
            foreach ($this->_customdata['dimensions'] as $key => $label) {
                $name = $side . 'filters[' . $key . ']';
                $form->addElement('text', $name, $label, ['maxlength' => 255]);
                $form->setType($name, PARAM_TEXT);
                $form->addHelpButton($name, 'migrationfilter', 'local_filebank');
            }
        }
    }

    /**
     * Define the correspondence rows controls.
     *
     * @param \MoodleQuickForm $form Native form element factory.
     * @return array Initial correspondence rows.
     */
    private function correspondence_rows(\MoodleQuickForm $form): array {
        $form->addElement('header', 'placementreview', get_string('migrationrules', 'local_filebank'));
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
                if ($side === 'source') {
                    $row['sourcetype'] = $predicate['type'] ?? 'folder';
                }
                $method = isset($predicate['pattern']) ? 'pattern' : (isset($predicate['template']) ? 'template' : 'equals');
                $row[$side . 'field'] = $predicate['field'] ?? 'name';
                $row[$side . 'method'] = $method;
                $row[$side . 'value'] = $predicate[$method];
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Define the correspondence fields controls.
     *
     * @param \MoodleQuickForm $form Native form element factory.
     * @param array $rows Initial correspondence rows.
     */
    private function correspondence_fields(\MoodleQuickForm $form, array $rows): void {
        $form->addElement(
            'textarea',
            'correspondencesjson',
            get_string('foldercorrespondences', 'local_filebank'),
            ['rows' => 8, 'id' => 'id_correspondencesjson']
        );
        $form->setType('correspondencesjson', PARAM_RAW);
        $form->setDefault('correspondencesjson', json_encode($rows));
        $moduleoptions = [];
        foreach (\local_filebank\local\catalog\source_registry::module_names() as $module => $label) {
            $moduleoptions[] = ['value' => $module, 'label' => $label];
        }
        $columns = [['key' => 'sourcetype', 'label' => get_string('modulefilter', 'local_filebank'),
            'type' => 'text', 'options' => $moduleoptions]];
        foreach (['source', 'target'] as $side) {
            $columns[] = ['key' => $side . 'field', 'label' => get_string($side . 'folderfield', 'local_filebank'),
                'type' => 'text', 'options' => [['value' => 'name', 'label' => get_string('name')],
                ['value' => 'idnumber', 'label' => get_string('idnumber')]]];
            $columns[] = ['key' => $side . 'method', 'label' => get_string('matchmethod', 'local_filebank'),
                'type' => 'text', 'options' => [['value' => 'equals', 'label' => get_string('exactmatch', 'local_filebank')],
                ['value' => 'pattern', 'label' => get_string('regexmatch', 'local_filebank')],
                ['value' => 'template', 'label' => get_string('templatematch', 'local_filebank')]]];
            $columns[] = ['key' => $side . 'value', 'label' => get_string($side . 'foldervalue', 'local_filebank'),
                'type' => 'text', 'maxlength' => 2048];
        }
        row_data::enhance($form, 'correspondencesjson', $columns, 20);
    }

    /**
     * Define the destination fields controls.
     *
     * @param \MoodleQuickForm $form Native form element factory.
     */
    private function destination_fields(\MoodleQuickForm $form): void {
        $migration = $this->_customdata['migration'] ?? [];

        $choices = [
            'sectionstrategy' => ['ordinal' => 'section_ordinal', 'name' => 'section_name', 'mapping' => 'section_mapping'],
            'duplicatepolicy' => ['skip-and-report' => 'duplicateskip', 'keep' => 'duplicatekeep'],
            'missingtarget' => ['block' => 'missingblock', 'create' => 'missingcreate'],
            'conflictpolicy' => ['block' => 'conflictblock', 'overwrite' => 'conflictoverwrite'],
        ];
        foreach ($choices as $name => $values) {
            $labels = [];
            foreach ($values as $key => $label) {
                $labels[$key] = get_string($label, 'local_filebank');
            }
            $form->addElement('select', $name, get_string($name, 'local_filebank'), $labels);
        }
        $form->setDefault('sectionstrategy', $migration['section_match']['strategy'] ?? 'ordinal');
        $form->setDefault('duplicatepolicy', $migration['same_content_different_path'] ?? 'skip-and-report');
        $form->setDefault('missingtarget', $migration['missing_target'] ?? 'block');
        $form->setDefault('conflictpolicy', $migration['same_path_different_content'] ?? 'block');
        $form->addElement(
            'textarea',
            'sectionmapjson',
            get_string('section_mapping', 'local_filebank'),
            ['rows' => 4, 'id' => 'id_sectionmapjson']
        );
        $form->setType('sectionmapjson', PARAM_RAW);
        $sectionrows = [];
        foreach ($migration['section_match']['map'] ?? [] as $source => $target) {
            $sectionrows[] = ['source' => (string)$source, 'target' => (string)$target];
        }
        $form->setDefault('sectionmapjson', json_encode($sectionrows));
        row_data::enhance(
            $form,
            'sectionmapjson',
            [
                ['key' => 'source', 'label' => get_string('sourcesectionid', 'local_filebank'), 'type' => 'text'],
                ['key' => 'target', 'label' => get_string('targetsectionid', 'local_filebank'), 'type' => 'text'],
            ],
            200
        );
        $form->hideIf('sectionmapjson', 'sectionstrategy', 'neq', 'mapping');
        $form->addElement('advcheckbox', 'layoutconfirmed', get_string('layoutconfirmed', 'local_filebank'));
        $form->hideIf('layoutconfirmed', 'sectionstrategy', 'neq', 'ordinal');
    }

    /**
     * Define the action fields controls.
     *
     * @param \MoodleQuickForm $form Native form element factory.
     */
    private function action_fields(\MoodleQuickForm $form): void {
        $buttons = [
            $form->createElement('submit', 'submitbutton', get_string('createpreview', 'local_filebank')),
        ];
        $form->addGroup($buttons, 'buttons', '', [' '], false);
        $form->closeHeaderBefore('buttons');
        foreach (
            ['correspondencesjson', 'sectionstrategy', 'duplicatepolicy', 'missingtarget',
            'conflictpolicy', 'sectionmapjson', 'layoutconfirmed'] as $name
        ) {
            $form->addHelpButton($name, $name, 'local_filebank');
        }
    }
}
