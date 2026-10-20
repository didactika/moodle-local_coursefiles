/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of the site: filtered, searched and paged by the server, with their state and what is left to decide in each one.
 *
 * @module     local_filebank/migration/components/list/MigrationList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useCallback, useEffect, useState} from 'react';
import {useDebounced} from '../../hooks/useDebounced';
import {describeError} from '../../lib/errors';
import {deleteMigrations, listMigrations} from '../../services/api';
import type {MigrationPage, Text} from '../../types';
import Alert from '../ui/Alert';
import Check from '../ui/Check';
import Chips from '../ui/Chips';
import ConfirmModal from '../ui/ConfirmModal';
import EmptyState from '../ui/EmptyState';
import FloatingBar from '../ui/FloatingBar';
import Icon from '../ui/Icon';
import PageHeader from '../ui/PageHeader';
import Pagination from '../ui/Pagination';
import Progress from '../ui/Progress';
import SearchBox from '../ui/SearchBox';
import Skeleton from '../ui/Skeleton';
import StatusBadge from '../ui/StatusBadge';

interface MigrationListProps {
    text: Text;
    pagesizes: number[];
    onOpen: (id: number) => void;
    onNew: () => void;
}

const STATES = ['', 'reviewable', 'queued', 'running', 'partial', 'completed', 'cancelled'];

const MigrationList = ({text, pagesizes, onOpen, onNew}: MigrationListProps) => {
    const [search, setSearch] = useState('');
    const [state, setState] = useState('');
    const [page, setPage] = useState(0);
    const [perpage, setPerpage] = useState(pagesizes[0] ?? 10);
    const [data, setData] = useState<MigrationPage | null>(null);
    const [error, setError] = useState('');
    const [selected, setSelected] = useState<number[]>([]);
    const [asking, setAsking] = useState<number[] | null>(null);
    const [deleting, setDeleting] = useState(false);
    const query = useDebounced(search, 250);

    const load = useCallback(async() => {
        try {
            setData(await listMigrations(query, state, page, perpage));
            setError('');
        } catch (problem) {
            setError(describeError(problem));
        }
    }, [query, state, page, perpage]);

    useEffect(() => {
        void load();
    }, [load]);

    useEffect(() => {
        setPage(0);
    }, [query, state, perpage]);

    // A migration that is running changes while the list is open.
    const active = data?.migrations.some((migration) => migration.state === 'queued' || migration.state === 'running') ?? false;
    useEffect(() => {
        if (!active) {
            return undefined;
        }
        const timer = window.setInterval(() => void load(), 4000);
        return () => window.clearInterval(timer);
    }, [active, load]);

    const countOf = (value: string) => data?.counts.find((item) => item.state === (value === '' ? 'all' : value))?.count ?? 0;
    const rows = data?.migrations ?? [];
    const hasAny = data !== null && (countOf('') > 0 || search !== '' || state !== '');
    const chosen = selected.filter((id) => rows.some((row) => row.id === id));
    const deletable = rows.filter((row) => row.candelete);

    const remove = async() => {
        if (!asking) {
            return;
        }
        setDeleting(true);
        try {
            await deleteMigrations(asking);
            setSelected([]);
            setAsking(null);
            await load();
        } catch (problem) {
            setError(describeError(problem));
            setAsking(null);
        } finally {
            setDeleting(false);
        }
    };

    return <div className="fbm-list" data-region="migration-list">
        <PageHeader
            title={text('list_title')}
            subtitle={text('list_subtitle')}
            actions={hasAny && <button type="button" className="fbm-btn fbm-btn-primary" onClick={onNew} data-action="new-migration">
                <Icon name="plus" gap />{text('new_migration')}
            </button>}
        />
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        {data === null && error === '' && <Skeleton rows={5} label={text('loading')} />}
        {data !== null && data.counts.find((item) => item.state === 'all')?.count === 0 && search === '' && state === '' && <EmptyState
            icon="right-left"
            title={text('list_empty_title')}
            text={text('list_empty_text')}
            actions={<button type="button" className="fbm-btn fbm-btn-primary" onClick={onNew} data-action="new-migration">
                <Icon name="plus" gap />{text('new_migration')}
            </button>}
        />}
        {data !== null && (countOf('') > 0 || search !== '' || state !== '') && <>
            <div className="fbm-toolbar">
                <SearchBox id="fbm-list-search" value={search} onChange={setSearch} placeholder={text('list_search')} clearLabel={text('clear')} />
                <select
                    className="fbm-select fbm-perpage"
                    aria-label={text('per_page')}
                    value={perpage}
                    onChange={(event) => setPerpage(Number(event.target.value))}
                >
                    {pagesizes.map((size) => <option key={size} value={size}>{text('per_page_n', {n: size})}</option>)}
                </select>
            </div>
            <Chips
                label={text('list_states')}
                value={state}
                onChange={setState}
                chips={STATES.map((value) => ({
                    value,
                    label: value === '' ? text('all') : text('state_' + value),
                    count: countOf(value),
                }))}
            />
            {rows.length === 0
                ? <EmptyState icon="magnifying-glass" title={text('list_nomatch_title')} text={text('list_nomatch_text')} />
                : <div className="fbm-table-wrap">
                    <table className="fbm-table">
                        <thead>
                            <tr>
                                <th className="fbm-col-check">
                                    <Check
                                        label={text('select_all_page')}
                                        checked={deletable.length > 0 && deletable.every((row) => chosen.includes(row.id))}
                                        partial={chosen.length > 0}
                                        disabled={deletable.length === 0}
                                        onChange={(checked) => setSelected(checked ? deletable.map((row) => row.id) : [])}
                                    />
                                </th>
                                <th>{text('col_migration')}</th>
                                <th>{text('col_route')}</th>
                                <th>{text('col_files')}</th>
                                <th>{text('col_state')}</th>
                                <th>{text('col_created')}</th>
                                <th><span className="fbm-sr">{text('col_actions')}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => <tr key={row.id} data-migration={row.id}>
                                <td className="fbm-col-check">
                                    <Check
                                        label={text('select_row', {n: row.id})}
                                        checked={chosen.includes(row.id)}
                                        disabled={!row.candelete}
                                        onChange={(checked) => setSelected(checked ? [...chosen, row.id] : chosen.filter((id) => id !== row.id))}
                                    />
                                </td>
                                <td>
                                    <button type="button" className="fbm-link" onClick={() => onOpen(row.id)}>
                                        <strong>#{row.id}</strong> {row.profile}
                                    </button>
                                </td>
                                <td>
                                    <span className="fbm-route">
                                        <span>{row.source}</span><Icon name="arrow-right" /><span>{row.target}</span>
                                    </span>
                                </td>
                                <td>
                                    <span className="fbm-files-cell">
                                        <strong>{row.towrite}</strong>
                                        <span className="fbm-muted">{text('list_of_files', {n: row.files})}</span>
                                        {row.conflicts > 0 && <span className="fbm-badge tone-info">
                                            <Icon name="hand-pointer" gap />{text('list_decisions', {n: row.conflicts})}
                                        </span>}
                                    </span>
                                </td>
                                <td>
                                    <StatusBadge status={row.state} label={row.statelabel} />
                                    {(row.state === 'running' || row.state === 'queued') && <Progress
                                        value={row.done}
                                        max={Math.max(1, row.towrite)}
                                        label={text('progress')}
                                    />}
                                </td>
                                <td className="fbm-muted">{row.datelabel}</td>
                                <td className="fbm-col-actions">
                                    <button type="button" className="fbm-btn fbm-btn-small" onClick={() => onOpen(row.id)}>
                                        {text('open')}<Icon name="arrow-right" />
                                    </button>
                                    {row.candelete && <button
                                        type="button"
                                        className="fbm-iconbtn tone-danger"
                                        title={text('delete')}
                                        onClick={() => setAsking([row.id])}
                                    >
                                        <Icon name="trash" /><span className="fbm-sr">{text('delete')}</span>
                                    </button>}
                                </td>
                            </tr>)}
                        </tbody>
                    </table>
                </div>}
            <Pagination page={page} perpage={perpage} total={data.total} onPage={setPage} text={text} />
        </>}
        {chosen.length > 0 && <FloatingBar
            summary={text('selected_n', {n: chosen.length})}
            clearLabel={text('clear_selection')}
            onClear={() => setSelected([])}
        >
            <button type="button" className="fbm-pill tone-danger" onClick={() => setAsking(chosen)}>
                <Icon name="trash" gap />{text('delete_selected')}
            </button>
        </FloatingBar>}
        <ConfirmModal
            open={asking !== null}
            tone="danger"
            icon="triangle-exclamation"
            busy={deleting}
            title={text('delete_title')}
            confirmLabel={text('delete')}
            cancelLabel={text('cancel')}
            onConfirm={() => void remove()}
            onCancel={() => setAsking(null)}
        >
            <p>{text('delete_text', {n: asking?.length ?? 0})}</p>
            <p className="fbm-muted">{text('delete_hint')}</p>
        </ConfirmModal>
    </div>;
};

export default MigrationList;
