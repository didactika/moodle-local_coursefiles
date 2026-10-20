/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which folder of the source goes into which folder of the destination: one card for each correspondence, with the way each side
 * is recognised. There is none to start with; the person adds the ones the migration needs.
 *
 * @module     local_filebank/migration/components/parameters/CorrespondenceRows
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {emptyCorrespondence, MAX_CORRESPONDENCES} from '../../lib/parameters';
import type {Correspondence, Options, Text} from '../../types';
import Icon from '../ui/Icon';
import IconButton from '../ui/IconButton';

interface CorrespondenceRowsProps {
    rows: Correspondence[];
    /** The last migrations of the profile, whose folders can be taken instead of writing them again. */
    previous: Options['previous'];
    onReuse: (id: number) => void;
    modules: Options['modules'];
    onChange: (rows: Correspondence[]) => void;
    text: Text;
    /** The person tried to go on with a row that is not complete, or with none. */
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
            <label className="form-label" htmlFor={id('type')}>{text('rules_kind')}</label>
            <select id={id('type')} className="form-select" value={row.sourcetype} onChange={(event) => onField('sourcetype', event.target.value)}>
                {modules.map((module) => <option key={module.value} value={module.value}>{module.label}</option>)}
            </select>
        </div>}
        <div className="fbm-field-row">
            <div className="fbm-field">
                <label className="form-label" htmlFor={id('field')}>{text('rules_field')}</label>
                <select
                    id={id('field')}
                    className="form-select"
                    value={row[key('field')]}
                    onChange={(event) => onField(key('field'), event.target.value)}
                >
                    {FIELDS.map((field) => <option key={field} value={field}>{text('rules_field_' + field)}</option>)}
                </select>
            </div>
            <div className="fbm-field">
                <label className="form-label" htmlFor={id('method')}>{text('rules_match')}</label>
                <select
                    id={id('method')}
                    className="form-select"
                    value={row[key('method')]}
                    onChange={(event) => onField(key('method'), event.target.value)}
                >
                    {METHODS.map((method) => <option key={method} value={method}>{text('rules_match_' + method)}</option>)}
                </select>
            </div>
        </div>
        <div className="fbm-field">
            <label className="form-label" htmlFor={id('value')}>{text('rules_value')}</label>
            <input
                id={id('value')}
                type="text"
                className={`form-control${missing && value.trim() === '' ? ' is-invalid' : ''}`}
                maxLength={2048}
                value={value}
                onChange={(event) => onField(key('value'), event.target.value)}
            />
        </div>
    </div>;
};

const CorrespondenceRows = ({rows, previous, onReuse, modules, onChange, text, missing}: CorrespondenceRowsProps) => {
    const change = (index: number, key: keyof Correspondence, value: string) => onChange(
        rows.map((row, position) => (position === index ? {...row, [key]: value} : row))
    );
    const room = rows.length < MAX_CORRESPONDENCES;

    return <div className="fbm-rows">
        {rows.length === 0 && <div className={`fbm-rows-empty${missing ? ' has-problem' : ''}`} data-region="no-correspondences">
            <div className="fbm-empty-icon"><Icon name="folder-open" /></div>
            <p className="text-muted fbm-empty-text">{text('rules_empty')}</p>
            {missing && <p className="fbm-problem">{text('rules_required')}</p>}
        </div>}
        {rows.map((row, index) => <div key={index} className="fbm-row-card">
            <div className="fbm-row-head">
                <span className="fbm-row-number">{index + 1}</span>
                <IconButton
                    icon="trash"
                    label={text('rules_remove')}
                    tone="danger"
                    onClick={() => onChange(rows.filter((_, position) => position !== index))}
                />
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
                className="btn btn-primary"
                disabled={!room}
                onClick={() => onChange([...rows, emptyCorrespondence()])}
                data-action="add-correspondence"
            >
                <Icon name="plus" gap />{text('rules_add')}
            </button>
            {previous.length > 0 && rows.length === 0 && <select
                className="form-select fbm-select-auto"
                aria-label={text('rules_reuse')}
                value=""
                onChange={(event) => onReuse(Number(event.target.value))}
                data-action="reuse-correspondences"
            >
                <option value="" disabled>{text('rules_reuse')}</option>
                {previous.map((item) => <option key={item.id} value={item.id}>{item.label}</option>)}
            </select>}
            <span className="text-muted">{text('rules_limit', {n: MAX_CORRESPONDENCES})}</span>
        </div>
    </div>;
};

export default CorrespondenceRows;
