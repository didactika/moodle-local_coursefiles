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
 * @package local_multipicker
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_multipicker\form;

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
        $form->addElement('select', 'editmode', get_string('editmode', 'local_multipicker'), [
            'guided' => get_string('guidedmode', 'local_multipicker'),
            'advanced' => get_string('advancedmode', 'local_multipicker'),
        ]);
        $form->setDefault('editmode', 'guided');
        $form->addElement('header', 'identitysettings', get_string('courseidentity', 'local_multipicker'));
        $form->addElement('select', 'coursefield', get_string('coursefield', 'local_multipicker'), [
            'idnumber' => get_string('idnumbercourse'), 'shortname' => get_string('shortnamecourse'),
            'fullname' => get_string('fullnamecourse'),
        ]);
        foreach (['coursepattern', 'familytemplate'] as $name) {
            $form->addElement('text', $name, get_string($name, 'local_multipicker'), ['class' => 'w-100']);
            $form->setType($name, PARAM_RAW);
        }
        foreach (['coursefield', 'coursepattern', 'familytemplate'] as $name) {
            $form->hideIf($name, 'editmode', 'eq', 'advanced');
        }
        foreach (['displaytemplate', 'editioncapture', 'pairdimensions'] as $name) {
            $form->addElement('text', $name, get_string($name, 'local_multipicker'), ['class' => 'w-100']);
            $form->setType($name, PARAM_RAW_TRIMMED);
            $form->hideIf($name, 'editmode', 'eq', 'advanced');
        }
        $form->addElement('advcheckbox', 'editionoptional', get_string('filteroptional', 'local_multipicker'));
        $form->hideIf('editionoptional', 'editmode', 'eq', 'advanced');
        $form->addElement('select', 'categoryids', get_string('categories'), \core_course_category::make_categories_list());
        $form->getElement('categoryids')->setMultiple(true);
        $form->hideIf('categoryids', 'editmode', 'eq', 'advanced');
        $form->addElement('hidden', 'guidedsettings', 1);
        $form->setType('guidedsettings', PARAM_BOOL);
        $form->addElement('header', 'capturefilters', get_string('capturefilters', 'local_multipicker'));
        $form->setExpanded('capturefilters', false);
        $form->addElement('static', 'filterhelp', '', get_string('capturefiltershelp', 'local_multipicker'));
        $filtercount = optional_param('filtercount', $this->_customdata['filtercount'] ?? 2, PARAM_INT);
        if (
            $filtercount < 0 || $filtercount > 12
                || ($filtercount === 12 && optional_param('addfilter', '', PARAM_RAW) !== '')
        ) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        $elements = [
            $form->createElement('text', 'filtercapture', get_string('filtercapture', 'local_multipicker')),
            $form->createElement('text', 'filterlabel', get_string('filterlabel', 'local_multipicker'), ['maxlength' => 255]),
            $form->createElement('advcheckbox', 'filteroptional', get_string('filteroptional', 'local_multipicker')),
            $form->createElement('hidden', 'filterkey'),
        ];
        $this->repeat_elements($elements, $this->_customdata['filtercount'] ?? 2, [
            'filtercapture' => ['type' => PARAM_ALPHANUMEXT], 'filterlabel' => ['type' => PARAM_RAW],
            'filteroptional' => ['type' => PARAM_BOOL], 'filterkey' => ['type' => PARAM_ALPHANUMEXT],
        ], 'filtercount', 'addfilter', 1, get_string('addfilter', 'local_multipicker'), true);
        if ($filtercount === 12) {
            $form->getElement('addfilter')->updateAttributes(['disabled' => 'disabled']);
        }
        $form->addElement('header', 'sourcesettings', get_string('publicsources', 'local_multipicker'));
        $form->setExpanded('sourcesettings', false);
        $form->addElement('static', 'sourceshelp', '', get_string('publicsourceshelp', 'local_multipicker'));
        $form->addElement('select', 'publicationmode', get_string('publicationmode', 'local_multipicker'), [
            'source-access-only' => get_string('publication_source-access-only', 'local_multipicker'),
            'explicit-family-publication' => get_string('publication_explicit-family-publication', 'local_multipicker'),
        ]);
        $form->hideIf('publicationmode', 'editmode', 'eq', 'advanced');
        $form->addElement('hidden', 'sourcesdefined', 1);
        $form->setType('sourcesdefined', PARAM_BOOL);
        foreach (self::source_controls() as $key => $area) {
            $label = $area === 'mod_*/intro' ? get_string('activityintroductions', 'local_multipicker')
                : get_string('modulename', substr(explode('/', $area)[0], 4));
            if (str_starts_with($area, 'mod_assign/')) {
                $label .= ' / ' . get_string(explode('/', $area)[1] . 's', 'mod_assign');
            }
            $form->addElement('advcheckbox', 'sources[' . $key . ']', $label);
            $form->setType('sources[' . $key . ']', PARAM_BOOL);
            $form->setDefault('sources[' . $key . ']', in_array($area, $this->_customdata['sources'] ?? [], true));
            $form->hideIf('sources[' . $key . ']', 'editmode', 'eq', 'advanced');
        }
        $form->addElement('header', 'migrationrules', get_string('migrationrules', 'local_multipicker'));
        $form->setExpanded('migrationrules', false);
        foreach (['source', 'target'] as $side) {
            $form->addElement('select', $side . 'folderfield', get_string($side . 'folderfield', 'local_multipicker'), [
                'name' => get_string('name'), 'idnumber' => get_string('idnumber'),
            ]);
            $form->addElement('select', $side . 'foldermatch', get_string('matchmethod', 'local_multipicker'), [
                'equals' => get_string('exactmatch', 'local_multipicker'),
                'pattern' => get_string('regexmatch', 'local_multipicker'),
                'template' => get_string('templatematch', 'local_multipicker'),
            ]);
            $form->addElement(
                'text',
                $side . 'foldervalue',
                get_string($side . 'foldervalue', 'local_multipicker'),
                ['class' => 'w-100', 'maxlength' => 2048]
            );
            $form->setType($side . 'foldervalue', PARAM_RAW_TRIMMED);
            foreach (['field', 'match', 'value'] as $part) {
                $form->hideIf($side . 'folder' . $part, 'editmode', 'eq', 'advanced');
            }
        }
        $form->addElement('select', 'sectionstrategy', get_string('sectionstrategy', 'local_multipicker'), [
            'ordinal' => get_string('section_ordinal', 'local_multipicker'),
            'name' => get_string('section_name', 'local_multipicker'),
            'mapping' => get_string('section_mapping', 'local_multipicker'),
        ]);
        $form->addElement('select', 'duplicatepolicy', get_string('duplicatepolicy', 'local_multipicker'), [
            'skip-and-report' => get_string('duplicateskip', 'local_multipicker'),
            'keep' => get_string('duplicatekeep', 'local_multipicker'),
        ]);
        $form->hideIf('sectionstrategy', 'editmode', 'eq', 'advanced');
        $form->hideIf('duplicatepolicy', 'editmode', 'eq', 'advanced');
        $form->addElement('header', 'rules', get_string('advancedmode', 'local_multipicker'));
        $form->setExpanded('rules', false);
        $form->addElement(
            'textarea',
            'configjson',
            get_string('profilejson', 'local_multipicker'),
            ['rows' => 14, 'class' => 'w-100']
        );
        $form->setType('configjson', PARAM_RAW);
        $form->hideIf('configjson', 'editmode', 'neq', 'advanced');
        $buttons = [];
        $buttons[] = $form->createElement('submit', 'saveprofile', get_string('saveprofile', 'local_multipicker'));
        $buttons[] = $form->createElement('submit', 'previewrules', get_string('previewrules', 'local_multipicker'));
        $buttons[] = $form->createElement('cancel');
        $form->addGroup($buttons, 'buttons', '', [' '], false);
        $form->closeHeaderBefore('buttons');
    }

    /**
     * Parse and validate the complete document before saving or previewing.
     *
     * @param array $data Submitted fields.
     * @param array $base Server-loaded configuration for guided editing.
     * @return array Validated configuration.
     */
    public static function document(array $data, array $base = []): array {
        try {
            $config = ($data['editmode'] ?? 'advanced') === 'guided' && $base ? $base
                : json_decode($data['configjson'] ?? '', true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        if (!is_array($config)) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        if (($data['editmode'] ?? 'advanced') === 'guided') {
            if (count($config['relationship']['parsers']) !== 1) {
                throw new \moodle_exception('advancedprofilerequired', 'local_multipicker');
            }
            $config['relationship']['parsers'][0]['field'] = $data['coursefield'];
            $config['relationship']['parsers'][0]['pattern'] = trim($data['coursepattern']);
            $config['relationship']['family_key'] = trim($data['familytemplate']);
            preg_match_all(
                '/\(\?(?:P?<([a-zA-Z][a-zA-Z0-9_]*)>|\x27([a-zA-Z][a-zA-Z0-9_]*)\x27)/',
                $config['relationship']['parsers'][0]['pattern'],
                $matches
            );
            $captures = array_filter(array_merge($matches[1], $matches[2]));
            $parser = &$config['relationship']['parsers'][0];
            $parser['required_captures'] = array_values(array_intersect($parser['required_captures'] ?? [], $captures));
            $parser['normalizers'] = array_intersect_key($parser['normalizers'] ?? [], array_flip($captures));
            preg_match_all('/\$\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $config['relationship']['display_label'] ?? '', $labels);
            if (array_diff($labels[1], $captures)) {
                $config['relationship']['display_label'] = $config['relationship']['family_key'];
            }
            $definitions = [];
            foreach ($data['filtercapture'] ?? [] as $index => $capture) {
                $label = trim($data['filterlabel'][$index] ?? '');
                if ($capture === '' && $label === '') {
                    continue;
                }
                $key = ($data['filterkey'][$index] ?? '') ?: $capture;
                if (isset($definitions[$key]) || $label === '') {
                    throw new \moodle_exception('invalidprofile', 'local_multipicker');
                }
                $definitions[$key] = ['capture' => $capture, 'label' => $label,
                    'optional' => !empty($data['filteroptional'][$index])];
            }
            $config['relationship']['filters'] = $definitions;
            if (!empty($data['sourcesdefined'])) {
                $controls = self::source_controls();
                $unrepresented = array_diff($config['publication']['allowed_sources'] ?? [], $controls);
                $config['publication']['allowed_sources'] = array_values(array_merge(
                    $unrepresented,
                    array_filter($controls, static fn($key) => !empty($data['sources'][$key]), ARRAY_FILTER_USE_KEY)
                ));
            }
            $config = \local_multipicker\local\profile\guided_config::apply($config, $data);
        }
        $config['name'] = trim($data['profilename']);
        if ($config['name'] === '' || \core_text::strlen($config['name']) > 255) {
            throw new \moodle_exception('invalidprofile', 'local_multipicker');
        }
        new \local_multipicker\local\profile\profile_config($config);
        return $config;
    }

    /**
     * Offer public core areas and community providers without duplicating individual introduction checkboxes.
     *
     * @return array Safe field keys mapped to declared source areas.
     */
    public static function source_controls(): array {
        $controls = [];
        foreach (\local_multipicker\local\catalog\source_registry::keys() as $area) {
            if ($area !== 'mod_*/intro' && str_ends_with($area, '/intro')) {
                continue;
            }
            $controls[sha1($area)] = $area;
        }
        return $controls;
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
            self::document($data, $this->_customdata['config'] ?? []);
        } catch (\moodle_exception $exception) {
            $errors[($data['editmode'] ?? 'advanced') === 'guided' ? 'coursepattern' : 'configjson'] = $exception->getMessage();
        }
        return $errors;
    }
}
