/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The folders a migration reads from and writes to. A switch puts every file of a folder in or out of the migration at once.
 *
 * @module     local_filebank/migration/components/review/FoldersPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {useDebounced} from '../../hooks/useDebounced';
import {describeError} from '../../lib/errors';
import {getFolders} from '../../services/api';
import type {FolderPage, Migration, MigrationFolder, Text} from '../../types';
import Alert from '../ui/Alert';
import EmptyState from '../ui/EmptyState';
import Icon from '../ui/Icon';
import Pagination from '../ui/Pagination';
import Panel from '../ui/Panel';
import Progress from '../ui/Progress';
import SearchBox from '../ui/SearchBox';
import Skeleton from '../ui/Skeleton';
import Switch from '../ui/Switch';

const PAGE_SIZE = 20;

interface FoldersPanelProps {
    migration: Migration;
    text: Text;
    busy: boolean;
    onSelect: (folder: MigrationFolder, included: boolean) => void;
}

const FoldersPanel = ({migration, text, busy, onSelect}: FoldersPanelProps) => {
    const [search, setSearch] = useState('');
    const [page, setPage] = useState(0);
    const [data, setData] = useState<FolderPage | null>(null);
    const [error, setError] = useState('');
    const query = useDebounced(search, 250);

    useEffect(() => {
        setPage(0);
    }, [query]);

    useEffect(() => {
        let current = true;
        void getFolders(migration.id, query, page, PAGE_SIZE).then((read) => {
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
    }, [migration.id, migration.digest, query, page]);

    const editable = migration.editable && !busy;

    return <Panel title={text('tab_folders')} icon="folder-tree" count={data?.total} region="folders">
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        <div className="fbm-toolbar">
            <SearchBox id="fbm-folders-search" value={search} onChange={setSearch} placeholder={text('folders_search')} clearLabel={text('clear')} />
        </div>
        {data === null && error === '' && <Skeleton rows={4} label={text('loading')} />}
        {data !== null && data.folders.length === 0 && <EmptyState plain icon="folder-open" title={text('folders_empty_title')} text={text('folders_empty_text')} />}
        {data !== null && data.folders.length > 0 && <ul className="fbm-folders">
            {data.folders.map((folder) => <li key={`${folder.sourcecourseid}:${folder.sourcecmid}:${folder.targetcourse}:${folder.targetfolder}`} className="fbm-folder">
                <Switch
                    label={text('folder_switch', {name: folder.sourcefolder})}
                    checked={folder.total > 0 && folder.included === folder.total}
                    partial={folder.included > 0 && folder.included < folder.total}
                    disabled={!editable}
                    onChange={(included) => onSelect(folder, included)}
                />
                <div className="fbm-folder-route">
                    <span className="fbm-folder-side">
                        <strong>{folder.sourcecourse}</strong>
                        <span className="text-muted">{folder.sourcefolder}</span>
                    </span>
                    <Icon name="arrow-right" />
                    <span className="fbm-folder-side">
                        <strong>{folder.targetcourse}</strong>
                        <span className="text-muted">{folder.targetfolder}</span>
                    </span>
                </div>
                <div className="fbm-folder-count">
                    <span>{text('folder_included', {included: folder.included, total: folder.total})}</span>
                    <Progress value={folder.included} max={Math.max(1, folder.total)} label={text('progress')} tone="success" />
                </div>
            </li>)}
        </ul>}
        <Pagination page={page} perpage={PAGE_SIZE} total={data?.total ?? 0} onPage={setPage} text={text} />
    </Panel>;
};

export default FoldersPanel;
