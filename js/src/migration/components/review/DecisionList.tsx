/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What waits for a decision of one nature, a page at a time. Some of it can be chosen and settled together, one thing can be
 * settled on its own row, and a long list does not become a long page.
 *
 * @module     local_filebank/migration/components/review/DecisionList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {describeError} from '../../lib/errors';
import {getDecisions} from '../../services/api';
import type {Choice, DecisionPage, Nature, Text} from '../../types';
import Alert from '../ui/Alert';
import Check from '../ui/Check';
import Icon from '../ui/Icon';
import Pagination from '../ui/Pagination';
import Skeleton from '../ui/Skeleton';

const PAGE_SIZE = 10;

interface DecisionListProps {
    migrationId: number;
    /** Changes whenever the migration does, so the page is read again. */
    digest: string;
    nature: Nature;
    text: Text;
    busy: boolean;
    onApply: (ids: number[], choice: string) => void;
}

/**
 * The choices that every one of some items offers.
 *
 * @param lists The choices of each item.
 * @returns The choices that are in all of them.
 */
const common = (lists: Choice[][]): Choice[] => {
    if (lists.length === 0) {
        return [];
    }
    return lists[0].filter((choice) => lists.every((list) => list.some((other) => other.value === choice.value)));
};

const DecisionList = ({migrationId, digest, nature, text, busy, onApply}: DecisionListProps) => {
    const [page, setPage] = useState(0);
    const [data, setData] = useState<DecisionPage | null>(null);
    const [error, setError] = useState('');
    const [selected, setSelected] = useState<number[]>([]);
    const [choice, setChoice] = useState('');
    const [own, setOwn] = useState<Record<number, string>>({});

    useEffect(() => {
        let current = true;
        void getDecisions(migrationId, nature.kind, nature.nature, page, PAGE_SIZE).then((read) => {
            if (current) {
                setData(read);
                setError('');
                setSelected([]);
                setOwn({});
            }
        }).catch((problem: unknown) => {
            if (current) {
                setError(describeError(problem));
            }
        });
        return () => {
            current = false;
        };
    }, [migrationId, digest, nature.kind, nature.nature, page]);

    // The page that was open can be gone when what it listed has been settled.
    useEffect(() => {
        if (data && data.items.length === 0 && page > 0) {
            setPage(page - 1);
        }
    }, [data, page]);

    if (error !== '') {
        return <Alert tone="danger" live>{error}</Alert>;
    }
    if (!data) {
        return <Skeleton rows={3} label={text('loading')} />;
    }

    const items = data.items;
    const chosen = items.filter((item) => selected.includes(item.id));
    const shared = common(chosen.map((item) => item.choices));
    const selectedChoice = shared.some((item) => item.value === choice) ? choice : shared[0]?.value ?? '';

    return <div className="fbm-decisions" data-region="decision-list">
        <div className="fbm-decisions-bar">
            <Check
                label={text('select_all_page')}
                checked={items.length > 0 && chosen.length === items.length}
                partial={chosen.length > 0}
                onChange={(checked) => setSelected(checked ? items.map((item) => item.id) : [])}
            />
            <span className="fbm-muted">{text('selected_n', {n: chosen.length})}</span>
            {chosen.length > 0 && <>
                <select className="fbm-select" aria-label={text('decision_choice')} value={selectedChoice} onChange={(event) => setChoice(event.target.value)}>
                    {shared.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                </select>
                <button
                    type="button"
                    className="fbm-btn fbm-btn-primary"
                    disabled={busy || selectedChoice === ''}
                    onClick={() => onApply(chosen.map((item) => item.id), selectedChoice)}
                >
                    <Icon name="check" gap />{text('apply_selected')}
                </button>
            </>}
        </div>
        <ul className="fbm-decision-items">
            {items.map((item) => {
                const value = own[item.id] ?? item.choices[0]?.value ?? '';
                return <li key={item.id} className="fbm-decision-item">
                    <Check
                        label={item.label}
                        checked={selected.includes(item.id)}
                        onChange={(checked) => setSelected(checked ? [...selected, item.id] : selected.filter((id) => id !== item.id))}
                    />
                    <div className="fbm-decision-text">
                        <strong>{item.label}</strong>
                        <span className="fbm-muted">{item.context}</span>
                        {item.detail !== '' && <span className="fbm-muted fbm-small">{item.detail}</span>}
                    </div>
                    <div className="fbm-decision-own">
                        {item.choices.length > 1 && <select
                            className="fbm-select"
                            aria-label={text('decision_choice')}
                            value={value}
                            onChange={(event) => setOwn({...own, [item.id]: event.target.value})}
                        >
                            {item.choices.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>}
                        <button
                            type="button"
                            className="fbm-btn fbm-btn-small"
                            disabled={busy || value === ''}
                            onClick={() => onApply([item.id], value)}
                            title={item.choices.length === 1 ? item.choices[0].label : text('apply')}
                        >
                            {item.choices.length === 1 ? item.choices[0].label : text('apply')}
                        </button>
                    </div>
                </li>;
            })}
        </ul>
        <Pagination page={page} perpage={PAGE_SIZE} total={data.total} onPage={setPage} text={text} />
    </div>;
};

export default DecisionList;
