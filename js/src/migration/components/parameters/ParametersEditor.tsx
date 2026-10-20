/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One step of what a person decides about a migration before it is worked out: the profile, the courses it goes between, which
 * folders go where, how sections match and what to do when something does not match. The same steps start a new migration and
 * change one that has not run.
 *
 * @module     local_filebank/migration/components/parameters/ParametersEditor
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {parametersFor, parametersOf, type Problem, type StepKey} from '../../lib/parameters';
import {getMigration} from '../../services/api';
import type {Options, Parameters, Text} from '../../types';
import CorrespondenceRows from './CorrespondenceRows';
import CourseFilters from './CourseFilters';
import PolicyFields from './PolicyFields';
import ProfilePicker from './ProfilePicker';
import SectionRules from './SectionRules';

interface ParametersEditorProps {
    step: StepKey;
    options: Options;
    parameters: Parameters;
    onChange: (parameters: Parameters) => void;
    /** Ask again for the options: the profiles whose name has the text, and the earlier migrations of the chosen profile. */
    onOptions: (search: string, profileid: number) => void;
    text: Text;
    /** The profile is fixed, because the migration already exists. */
    lockProfile?: boolean;
    /** What the person tried to go on without. */
    problems: Problem[];
}

const ParametersEditor = ({step, options, parameters, onChange, onOptions, text, lockProfile = false, problems}: ParametersEditorProps) => {
    const profile = options.profiles.find((item) => item.id === parameters.profileid);

    if (step === 'profile') {
        return <ProfilePicker
            profiles={options.profiles}
            chosen={parameters.profileid}
            locked={lockProfile}
            invalid={problems.includes('profile')}
            onPick={(next) => {
                onChange(parametersFor(next));
                onOptions('', next.id);
            }}
            onSearch={(query) => onOptions(query, parameters.profileid)}
            text={text}
        />;
    }
    if (!profile) {
        return null;
    }
    if (step === 'courses') {
        return <CourseFilters
            dimensions={profile.dimensions}
            parameters={parameters}
            onChange={onChange}
            text={text}
            missing={problems.includes('courses')}
        />;
    }
    if (step === 'folders') {
        const reuse = async(id: number) => {
            const earlier = parametersOf(await getMigration(id));
            onChange({...parameters, rules: {...parameters.rules, correspondences: earlier.rules.correspondences}});
        };
        return <CorrespondenceRows
            rows={parameters.rules.correspondences}
            previous={options.previous}
            onReuse={(id) => void reuse(id).catch(() => undefined)}
            modules={options.modules}
            onChange={(correspondences) => onChange({...parameters, rules: {...parameters.rules, correspondences}})}
            text={text}
            missing={problems.includes('correspondence')}
        />;
    }
    if (step === 'sections') {
        return <SectionRules
            rules={parameters.rules}
            onChange={(rules) => onChange({...parameters, rules})}
            text={text}
            missing={problems.includes('sections')}
        />;
    }
    return <PolicyFields rules={parameters.rules} onChange={(rules) => onChange({...parameters, rules})} text={text} />;
};

export default ParametersEditor;
