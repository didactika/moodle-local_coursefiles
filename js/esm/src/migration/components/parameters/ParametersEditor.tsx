/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Everything a person decides about a migration before it is worked out: the courses it goes between, which folders go where and
 * what to do when something does not match. The same editor starts a new migration and changes one that has not run.
 *
 * @module     local_filebank/migration/components/parameters/ParametersEditor
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import {parametersFor, type Problem} from '../../lib/parameters';
import type {Options, Parameters, Text} from '../../types';
import Icon from '../ui/Icon';
import CorrespondenceRows from './CorrespondenceRows';
import CourseFilters from './CourseFilters';
import PolicyFields from './PolicyFields';
import SectionRules from './SectionRules';

interface ParametersEditorProps {
    options: Options;
    parameters: Parameters;
    onChange: (parameters: Parameters) => void;
    text: Text;
    /** The profile is fixed, because the migration already exists. */
    lockProfile?: boolean;
    /** What the person tried to go on without. */
    problems: Problem[];
}

interface BlockProps {
    icon: string;
    title: string;
    help: string;
    children: ReactNode;
}

/**
 * A titled block of the editor.
 *
 * @param props The title, what it is for and its fields.
 * @returns The block.
 */
const Block = ({icon, title, help, children}: BlockProps) => <section className="fbm-section">
    <header className="fbm-section-head">
        <span className="fbm-section-icon"><Icon name={icon} /></span>
        <div>
            <h4 className="fbm-h4">{title}</h4>
            <p className="fbm-muted fbm-section-help">{help}</p>
        </div>
    </header>
    {children}
</section>;

const ParametersEditor = ({options, parameters, onChange, text, lockProfile = false, problems}: ParametersEditorProps) => {
    const profile = options.profiles.find((item) => item.id === parameters.profileid);

    return <div className="fbm-editor">
        <Block icon="layer-group" title={text('wizard_profile')} help={text('wizard_profile_help')}>
            <select
                className={`fbm-select${problems.includes('profile') ? ' is-invalid' : ''}`}
                aria-label={text('wizard_profile')}
                value={parameters.profileid}
                disabled={lockProfile}
                onChange={(event) => {
                    const next = options.profiles.find((item) => item.id === Number(event.target.value));
                    if (next) {
                        onChange(parametersFor(next));
                    }
                }}
            >
                {parameters.profileid <= 0 && <option value={0}>{text('wizard_profile_choose')}</option>}
                {options.profiles.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
        </Block>
        {profile && <>
            <Block icon="graduation-cap" title={text('wizard_courses')} help={text('wizard_courses_help')}>
                <CourseFilters
                    dimensions={profile.dimensions}
                    parameters={parameters}
                    onChange={onChange}
                    text={text}
                    missing={problems.includes('courses')}
                />
            </Block>
            <Block icon="folder-tree" title={text('wizard_folders')} help={text('wizard_folders_help')}>
                <CorrespondenceRows
                    rows={parameters.rules.correspondences}
                    modules={options.modules}
                    onChange={(correspondences) => onChange({...parameters, rules: {...parameters.rules, correspondences}})}
                    text={text}
                    missing={problems.includes('correspondence')}
                />
            </Block>
            <Block icon="list-ol" title={text('wizard_sections')} help={text('wizard_sections_help')}>
                <SectionRules
                    rules={parameters.rules}
                    onChange={(rules) => onChange({...parameters, rules})}
                    text={text}
                    missing={problems.includes('sections')}
                />
            </Block>
            <Block icon="sliders" title={text('wizard_policies')} help={text('wizard_policies_help')}>
                <PolicyFields rules={parameters.rules} onChange={(rules) => onChange({...parameters, rules})} text={text} />
            </Block>
        </>}
    </div>;
};

export default ParametersEditor;
