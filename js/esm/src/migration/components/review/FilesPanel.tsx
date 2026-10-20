/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The files of a migration: filtered by what would happen to them, searched and paged by the server. A file is put in or out of
 * the migration with its check box, and every file of the migration with one button.
 *
 * @module     local_filebank/migration/components/review/FilesPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {useDebounced} from '../../hooks/useDebounced';
import {describeError} from '../../lib/errors';
import {getFiles} from '../../services/api';
import type {Bucket, FilePage, Migration, Text} from '../../types';
import Alert from '../ui/Alert';
import Check from '../ui/Check';
import Chips from '../ui/Chips';
import EmptyState from '../ui/EmptyState';
import Icon from '../ui/Icon';
import Panel from '../ui/Panel';
import Pagination from '../ui/Pagination';
import SearchBox from '../ui/SearchBox';
import Skeleton from '../ui/Skeleton';
import StatusBadge from '../ui/StatusBadge';

/** The pages of files are at most as long as a selection that the server accepts for one page. */
const SIZES = [10, 25, 50];

interface FilesPanelProps {
    migration: Migration;
    text: Text;
    bucket: Bucket;
    onBucket: (bucket: Bucket) => void;
    busy: boolean;
    /** Put the files of this page in or out, or every file of the migration. */
    onSelect: (action: 'page' | 'all' | 'none', visible?: number[], included?: number[]) => void;
}

const BUCKETS: Bucket[] = ['', 'copy', 'existing', 'conflict', 'left'];

const FilesPanel = ({migration, text, bucket, onBucket, busy, onSelect}: FilesPanelProps) => {
    const [search, setSearch] = useState('');
    const [page, setPage] = useState(0);
    const [perpage, setPerpage] = useState(SIZES[1]);
    const [data, setData] = useState<FilePage | null>(null);
    const [error, setError] = useState('');
    const query = useDebounced(search, 250);

    useEffect(() => {
        setPage(0);
    }, [bucket, query, perpage]);

    useEffect(() => {
        let current = true;
        void getFiles(migration.id, bucket, query, page, perpage).then((read) => {
            if (current) {
                setData(read);
                setError('');
            }
        }).catch((problem: unknown) => {
            if (current) {
                setError(describeError(problem));
            }
        });
        return () => {
            current = false;
        };
    }, [migration.id, migration.digest, bucket, query, page, perpage]);

    const countOf = (value: Bucket) => data?.counts.find((item) => item.bucket === (value === '' ? 'all' : value))?.count ?? 0;
    const files = data?.files ?? [];
    const editable = migration.editable && !busy;
    const settable = files.filter((file) => file.settable);
    const included = files.filter((file) => file.included);
    const showsRun = migration.run !== undefined;

    const toggle = (id: number, checked: boolean) => onSelect(
        'page',
        files.map((file) => file.id),
        files.filter((file) => (file.id === id ? checked : file.included)).map((file) => file.id)
    );

    const togglePage = (checked: boolean) => onSelect(
        'page',
        files.map((file) => file.id),
        files.filter((file) => (file.settable ? checked : file.included)).map((file) => file.id)
    );

    return <Panel
        title={text('tab_files')}
        icon="file-lines"
        count={countOf('')}
        region="files"
        actions={migration.editable && <>
            <button type="button" className="fbm-btn fbm-btn-small" disabled={busy} onClick={() => onSelect('all')}>
                <Icon name="square-check" gap />{text('include_all')}
            </button>
            <button type="button" className="fbm-btn fbm-btn-small" disabled={busy} onClick={() => onSelect('none')}>
                <Icon name="square" gap />{text('exclude_all')}
            </button>
        </>}
    >
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        <div className="fbm-toolbar">
            <SearchBox id="fbm-files-search" value={search} onChange={setSearch} placeholder={text('files_search')} clearLabel={text('clear')} />
            <select className="fbm-select fbm-perpage" aria-label={text('per_page')} value={perpage} onChange={(event) => setPerpage(Number(event.target.value))}>
                {SIZES.map((size) => <option key={size} value={size}>{text('per_page_n', {n: size})}</option>)}
            </select>
        </div>
        <Chips
            label={text('files_groups')}
            value={bucket}
            onChange={(value) => onBucket(value as Bucket)}
            chips={BUCKETS.map((value) => ({
                value,
                label: text(value === '' ? 'all' : 'bucket_' + value),
                count: countOf(value),
            }))}
        />
        {data === null && error === '' && <Skeleton rows={6} label={text('loading')} />}
        {data !== null && files.length === 0 && <EmptyState plain icon="file-circle-question" title={text('files_empty_title')} text={text('files_empty_text')} />}
        {files.length > 0 && <div className="fbm-table-wrap">
            <table className="fbm-table fbm-files">
                <thead>
                    <tr>
                        <th className="fbm-col-check">
                            <Check
                                label={text('select_all_page')}
                                checked={settable.length > 0 && settable.every((file) => file.included)}
                                partial={included.length > 0}
                                disabled={!editable || settable.length === 0}
                                onChange={togglePage}
                            />
                        </th>
                        <th>{text('col_file')}</th>
                        <th>{text('col_from')}</th>
                        <th>{text('col_to')}</th>
                        <th>{text('col_decision')}</th>
                        {showsRun && <th>{text('col_result')}</th>}
                    </tr>
                </thead>
                <tbody>
                    {files.map((file) => <tr key={file.id} className={file.included ? '' : 'is-out'} data-file={file.id}>
                        <td className="fbm-col-check">
                            <Check
                                label={file.filename}
                                checked={file.included}
                                disabled={!editable || !file.settable}
                                onChange={(checked) => toggle(file.id, checked)}
                            />
                        </td>
                        <td>
                            <strong className="fbm-file-name">{file.filename}</strong>
                            {file.filepath !== '/' && <span className="fbm-muted fbm-small fbm-block">{file.filepath}</span>}
                        </td>
                        <td><span className="fbm-block">{file.sourcecourse}</span><span className="fbm-muted fbm-small">{file.sourcefolder}</span></td>
                        <td><span className="fbm-block">{file.targetcourse}</span><span className="fbm-muted fbm-small">{file.targetfolder}</span></td>
                        <td><StatusBadge status={file.decision} label={file.decisionlabel} /></td>
                        {showsRun && <td>
                            {file.included && <StatusBadge status={file.state} label={file.statelabel} />}
                            {file.result !== '' && <span className="fbm-muted fbm-small fbm-block">{file.result}</span>}
                        </td>}
                    </tr>)}
                </tbody>
            </table>
        </div>}
        <Pagination page={page} perpage={perpage} total={data?.total ?? 0} onPage={setPage} text={text} />
    </Panel>;
};

export default FilesPanel;
