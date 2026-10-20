/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * How the sections of a source course are matched with the sections of the destination course: by position, by name, or pair by
 * pair.
 *
 * @module     local_filebank/migration/components/parameters/SectionRules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {MAX_SECTION_PAIRS} from '../../lib/parameters';
import type {Rules, Text} from '../../types';
import ChoiceCards from '../ui/ChoiceCards';
import Icon from '../ui/Icon';
import IconButton from '../ui/IconButton';

interface SectionRulesProps {
    rules: Rules;
    onChange: (rules: Rules) => void;
    text: Text;
    missing: boolean;
}

const STRATEGIES = ['ordinal', 'name', 'mapping'];

const SectionRules = ({rules, onChange, text, missing}: SectionRulesProps) => {
    const pairs = rules.sectionmap;
    const setPair = (index: number, key: 'source' | 'target', value: string) => onChange({
        ...rules,
        sectionmap: pairs.map((pair, position) => (position === index ? {...pair, [key]: value} : pair)),
    });
    const invalid = (value: string) => missing && !/^[1-9][0-9]*$/.test(value);

    return <div className="fbm-sections">
        <ChoiceCards
            label={text('rules_sections')}
            name="fbm-section-strategy"
            value={rules.sectionstrategy}
            onChange={(sectionstrategy) => onChange({
                ...rules,
                sectionstrategy,
                sectionmap: sectionstrategy === 'mapping' && pairs.length === 0 ? [{source: '', target: ''}] : pairs,
            })}
            choices={STRATEGIES.map((value) => ({
                value,
                label: text('rules_sections_' + value),
                hint: text('rules_sections_' + value + '_hint'),
            }))}
        />
        {rules.sectionstrategy === 'mapping' && <div className="fbm-pairs">
            <div className="fbm-pair fbm-pair-head" aria-hidden="true">
                <span />
                <span className="form-label">{text('rules_section_source')}</span>
                <span />
                <span className="form-label">{text('rules_section_target')}</span>
                <span />
            </div>
            {pairs.map((pair, index) => <div key={index} className="fbm-pair">
                <span className="fbm-row-number">{index + 1}</span>
                <input
                    type="number"
                    min={1}
                    inputMode="numeric"
                    className={`form-control${invalid(pair.source) ? ' is-invalid' : ''}`}
                    value={pair.source}
                    aria-label={text('rules_section_source')}
                    onChange={(event) => setPair(index, 'source', event.target.value)}
                />
                <Icon name="arrow-right" />
                <input
                    type="number"
                    min={1}
                    inputMode="numeric"
                    className={`form-control${invalid(pair.target) ? ' is-invalid' : ''}`}
                    value={pair.target}
                    aria-label={text('rules_section_target')}
                    onChange={(event) => setPair(index, 'target', event.target.value)}
                />
                <IconButton
                    icon="trash"
                    label={text('rules_remove')}
                    tone="danger"
                    onClick={() => onChange({...rules, sectionmap: pairs.filter((_, position) => position !== index)})}
                />
            </div>)}
            <div className="fbm-row-add">
                <button
                    type="button"
                    className="btn btn-outline-secondary"
                    disabled={pairs.length >= MAX_SECTION_PAIRS}
                    onClick={() => onChange({...rules, sectionmap: [...pairs, {source: '', target: ''}]})}
                >
                    <Icon name="plus" gap />{text('rules_section_add')}
                </button>
            </div>
        </div>}
    </div>;
};

export default SectionRules;
