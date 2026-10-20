/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The courses a migration goes between: what the courses of each side have to have, one field for each filter of the profile.
 *
 * @module     local_filebank/migration/components/parameters/CourseFilters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Dimension, Parameters, Text} from '../../types';
import Icon from '../ui/Icon';

interface CourseFiltersProps {
    dimensions: Dimension[];
    parameters: Parameters;
    onChange: (parameters: Parameters) => void;
    text: Text;
    /** The person tried to go on without choosing any course. */
    missing: boolean;
}

const CourseFilters = ({dimensions, parameters, onChange, text, missing}: CourseFiltersProps) => {
    const set = (side: 'source' | 'target', key: string, value: string) => {
        const next = {...parameters[side]};
        if (value === '') {
            delete next[key];
        } else {
            next[key] = value;
        }
        onChange({...parameters, [side]: next});
    };

    if (dimensions.length === 0) {
        return <p className="fbm-muted">{text('wizard_no_filters')}</p>;
    }

    return <div className={`fbm-sides${missing ? ' has-problem' : ''}`}>
        {(['source', 'target'] as const).map((side) => <fieldset key={side} className={`fbm-side fbm-side-${side}`}>
            <legend className="fbm-side-title">
                <Icon name={side === 'source' ? 'arrow-right-from-bracket' : 'arrow-right-to-bracket'} gap />
                {text(side === 'source' ? 'wizard_from' : 'wizard_to')}
            </legend>
            {dimensions.map((dimension) => <div key={dimension.key} className="fbm-field">
                <label className="fbm-label" htmlFor={`fbm-${side}-${dimension.key}`}>{dimension.label}</label>
                <input
                    id={`fbm-${side}-${dimension.key}`}
                    type="text"
                    className="fbm-input"
                    maxLength={255}
                    value={parameters[side][dimension.key] ?? ''}
                    placeholder={text('wizard_any')}
                    onChange={(event) => set(side, dimension.key, event.target.value)}
                />
            </div>)}
        </fieldset>)}
        {missing && <p className="fbm-problem">{text('wizard_courses_required')}</p>}
    </div>;
};

export default CourseFilters;
