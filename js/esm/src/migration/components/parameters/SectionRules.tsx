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
import Chips from '../ui/Chips';
import Icon from '../ui/Icon';

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

    return <div className="fbm-sections">
        <Chips
            label={text('rules_sections')}
            value={rules.sectionstrategy}
            onChange={(sectionstrategy) => onChange({
                ...rules,
                sectionstrategy,
                sectionmap: sectionstrategy === 'mapping' && pairs.length === 0 ? [{source: '', target: ''}] : pairs,
            })}
            chips={STRATEGIES.map((value) => ({value, label: text('rules_sections_' + value)}))}
        />
        <p className="fbm-muted fbm-hint">{text('rules_sections_' + rules.sectionstrategy + '_hint')}</p>
        {rules.sectionstrategy === 'mapping' && <div className="fbm-pairs">
            {pairs.map((pair, index) => <div key={index} className="fbm-pair">
                <input
                    type="text"
                    inputMode="numeric"
                    className={`fbm-input${missing && !/^[1-9][0-9]*$/.test(pair.source) ? ' is-invalid' : ''}`}
                    value={pair.source}
                    aria-label={text('rules_section_source')}
                    placeholder={text('rules_section_source')}
                    onChange={(event) => setPair(index, 'source', event.target.value)}
                />
                <Icon name="arrow-right" />
                <input
                    type="text"
                    inputMode="numeric"
                    className={`fbm-input${missing && !/^[1-9][0-9]*$/.test(pair.target) ? ' is-invalid' : ''}`}
                    value={pair.target}
                    aria-label={text('rules_section_target')}
                    placeholder={text('rules_section_target')}
                    onChange={(event) => setPair(index, 'target', event.target.value)}
                />
                <button
                    type="button"
                    className="fbm-iconbtn"
                    title={text('rules_remove')}
                    onClick={() => onChange({...rules, sectionmap: pairs.filter((_, position) => position !== index)})}
                >
                    <Icon name="trash" /><span className="fbm-sr">{text('rules_remove')}</span>
                </button>
            </div>)}
            <button
                type="button"
                className="fbm-btn"
                disabled={pairs.length >= MAX_SECTION_PAIRS}
                onClick={() => onChange({...rules, sectionmap: [...pairs, {source: '', target: ''}]})}
            >
                <Icon name="plus" gap />{text('rules_section_add')}
            </button>
        </div>}
    </div>;
};

export default SectionRules;
