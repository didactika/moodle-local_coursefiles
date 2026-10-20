/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. None of the choices stops a migration: what is not decided now is decided afterwards,
 * file by file or all at once, inside the same migration.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Rules, Text} from '../../types';
import Chips from '../ui/Chips';

interface PolicyFieldsProps {
    rules: Rules;
    onChange: (rules: Rules) => void;
    text: Text;
}

interface Policy {
    key: 'missingtarget' | 'conflictpolicy' | 'duplicatepolicy';
    values: string[];
}

const POLICIES: Policy[] = [
    {key: 'missingtarget', values: ['create', 'skip']},
    {key: 'conflictpolicy', values: ['block', 'overwrite']},
    {key: 'duplicatepolicy', values: ['skip-and-report', 'keep']},
];

const PolicyFields = ({rules, onChange, text}: PolicyFieldsProps) => <div className="fbm-policies">
    {POLICIES.map((policy) => {
        // A migration that was stored before there was a choice can carry a value that is not offered any more.
        const value = policy.values.includes(rules[policy.key]) ? rules[policy.key] : policy.values[0];
        // The names of the texts cannot have a hyphen.
        const name = (item: string) => 'policy_' + policy.key + '_' + item.split('-').join('_');
        return <div key={policy.key} className="fbm-policy">
            <div className="fbm-label">{text('policy_' + policy.key)}</div>
            <Chips
                label={text('policy_' + policy.key)}
                value={value}
                onChange={(next) => onChange({...rules, [policy.key]: next})}
                chips={policy.values.map((item) => ({value: item, label: text(name(item))}))}
            />
            <p className="fbm-muted fbm-hint">{text(name(value) + '_hint')}</p>
        </div>;
    })}
</div>;

export default PolicyFields;
