/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which folder of the source goes into which folder of the destination: one card for each correspondence, with the way each side
 * is recognised.
 *
 * @module     local_filebank/migration/components/parameters/CorrespondenceRows
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {MAX_CORRESPONDENCES, sameNameCorrespondence} from '../../lib/parameters';
import type {Correspondence, Options, Text} from '../../types';
import Icon from '../ui/Icon';

interface CorrespondenceRowsProps {
    rows: Correspondence[];
    modules: Options['modules'];
    onChange: (rows: Correspondence[]) => void;
    text: Text;
    /** The person tried to go on with a row that is not complete. */
    missing: boolean;
}

const FIELDS = ['name', 'idnumber'];
const METHODS = ['equals', 'pattern', 'template'];

interface SideProps {
    side: 'source' | 'target';
    index: number;
    row: Correspondence;
    modules: Options['modules'];
    onField: (key: keyof Correspondence, value: string) => void;
    text: Text;
    missing: boolean;
}

/**
 * The fields that tell one side of a correspondence.
 *
 * @param props The side, the row and how to change it.
 * @returns The fields.
 */
const Side = ({side, index, row, modules, onField, text, missing}: SideProps) => {
    const key = (name: string) => `${side}${name}` as keyof Correspondence;
    const id = (name: string) => `fbm-c${index}-${side}-${name}`;
    const value = row[key('value')];
    return <div className={`fbm-side fbm-side-${side}`}>
        <div className="fbm-side-title">{text(side === 'source' ? 'rules_source_folder' : 'rules_target_folder')}</div>
        {side === 'source' && <div className="fbm-field">
            <label className="fbm-label" htmlFor={id('type')}>{text('rules_kind')}</label>
            <select id={id('type')} className="fbm-select" value={row.sourcetype} onChange={(event) => onField('sourcetype', event.target.value)}>
                {modules.map((module) => <option key={module.value} value={module.value}>{module.label}</option>)}
            </select>
        </div>}
        <div className="fbm-field-row">
            <div className="fbm-field">
                <label className="fbm-label" htmlFor={id('field')}>{text('rules_field')}</label>
                <select id={id('field')} className="fbm-select" value={row[key('field')]} onChange={(event) => onField(key('field'), event.target.value)}>
                    {FIELDS.map((field) => <option key={field} value={field}>{text('rules_field_' + field)}</option>)}
                </select>
            </div>
            <div className="fbm-field">
                <label className="fbm-label" htmlFor={id('method')}>{text('rules_match')}</label>
                <select id={id('method')} className="fbm-select" value={row[key('method')]} onChange={(event) => onField(key('method'), event.target.value)}>
                    {METHODS.map((method) => <option key={method} value={method}>{text('rules_match_' + method)}</option>)}
                </select>
            </div>
        </div>
        <div className="fbm-field">
            <label className="fbm-label" htmlFor={id('value')}>{text('rules_value')}</label>
            <input
                id={id('value')}
                type="text"
                className={`fbm-input${missing && value.trim() === '' ? ' is-invalid' : ''}`}
                maxLength={2048}
                value={value}
                onChange={(event) => onField(key('value'), event.target.value)}
            />
        </div>
    </div>;
};

const CorrespondenceRows = ({rows, modules, onChange, text, missing}: CorrespondenceRowsProps) => {
    const change = (index: number, key: keyof Correspondence, value: string) => onChange(
        rows.map((row, position) => (position === index ? {...row, [key]: value} : row))
    );

    return <div className="fbm-rows">
        {rows.map((row, index) => <div key={index} className="fbm-row-card">
            <div className="fbm-row-head">
                <span className="fbm-row-number">{index + 1}</span>
                {rows.length > 1 && <button
                    type="button"
                    className="fbm-iconbtn"
                    onClick={() => onChange(rows.filter((_, position) => position !== index))}
                    title={text('rules_remove')}
                >
                    <Icon name="trash" /><span className="fbm-sr">{text('rules_remove')}</span>
                </button>}
            </div>
            <div className="fbm-row-sides">
                <Side side="source" index={index} row={row} modules={modules} text={text} missing={missing} onField={(key, value) => change(index, key, value)} />
                <span className="fbm-row-arrow"><Icon name="arrow-right" /></span>
                <Side side="target" index={index} row={row} modules={modules} text={text} missing={missing} onField={(key, value) => change(index, key, value)} />
            </div>
        </div>)}
        <div className="fbm-row-add">
            <button
                type="button"
                className="fbm-btn"
                disabled={rows.length >= MAX_CORRESPONDENCES}
                onClick={() => onChange([...rows, sameNameCorrespondence()])}
            >
                <Icon name="plus" gap />{text('rules_add')}
            </button>
            <span className="fbm-muted">{text('rules_limit', {n: MAX_CORRESPONDENCES})}</span>
        </div>
    </div>;
};

export default CorrespondenceRows;
