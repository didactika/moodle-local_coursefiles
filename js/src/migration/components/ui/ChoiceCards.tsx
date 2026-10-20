/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A choice between a few things that need to be explained: each one is a card with its name and what it does, and the chosen one is
 * marked. It is a group of real radio buttons, so the keyboard and assistive technology work as they do in a form.
 *
 * @module     local_filebank/migration/components/ui/ChoiceCards
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export interface ChoiceCard {
    value: string;
    label: string;
    /** What the choice does. */
    hint?: string;
}

interface ChoiceCardsProps {
    /** What is being chosen, which is read as the name of the group. */
    label: string;
    name: string;
    value: string;
    choices: ChoiceCard[];
    onChange: (value: string) => void;
}

const ChoiceCards = ({label, name, value, choices, onChange}: ChoiceCardsProps) => <div className="fbm-choices" role="radiogroup" aria-label={label}>
    {choices.map((choice) => <label key={choice.value} className={`fbm-choice${choice.value === value ? ' is-on' : ''}`}>
        <input
            type="radio"
            name={name}
            value={choice.value}
            checked={choice.value === value}
            onChange={() => onChange(choice.value)}
        />
        <span className="fbm-choice-dot" aria-hidden="true" />
        <span className="fbm-choice-text">
            <span className="fbm-choice-title">{choice.label}</span>
            {choice.hint && <span className="fbm-choice-hint">{choice.hint}</span>}
        </span>
    </label>)}
</div>;

export default ChoiceCards;
