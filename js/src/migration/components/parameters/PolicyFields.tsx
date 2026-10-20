/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. Each choice is a card that says what it does; none of them stops a migration. What
 * happens with a file that is already different in the destination is decided in the last step, where it is known how many there
 * are.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Rules, Text} from '../../types';
import ChoiceCards from '../ui/ChoiceCards';

interface PolicyFieldsProps {
    rules: Rules;
    onChange: (rules: Rules) => void;
    text: Text;
}

interface Policy {
    key: 'missingtarget' | 'duplicatepolicy';
    values: string[];
}

const POLICIES: Policy[] = [
    {key: 'missingtarget', values: ['create', 'skip']},
    {key: 'duplicatepolicy', values: ['skip-and-report', 'keep']},
];

const PolicyFields = ({rules, onChange, text}: PolicyFieldsProps) => <div className="fbm-policies">
    {POLICIES.map((policy) => {
        // A migration that was stored before there was a choice can carry a value that is not offered any more.
        const value = policy.values.includes(rules[policy.key]) ? rules[policy.key] : policy.values[0];
        // The names of the texts cannot have a hyphen.
        const name = (item: string) => 'policy_' + policy.key + '_' + item.split('-').join('_');
        return <fieldset key={policy.key} className="fbm-policy">
            <legend className="fbm-policy-title">{text('policy_' + policy.key)}</legend>
            <ChoiceCards
                label={text('policy_' + policy.key)}
                name={`fbm-policy-${policy.key}`}
                value={value}
                onChange={(next) => onChange({...rules, [policy.key]: next})}
                choices={policy.values.map((item) => ({value: item, label: text(name(item)), hint: text(name(item) + '_hint')}))}
            />
        </fieldset>;
    })}
</div>;

export default PolicyFields;
