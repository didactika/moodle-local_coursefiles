/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One migration: what it would do, what waits for a decision, its files and folders, and the way to start it. Everything a person
 * does here is saved in this migration; another one is only created from the button that says so.
 *
 * @module     local_filebank/migration/components/review/Review
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useMemo, useState} from 'react';
import {useMigration} from '../../hooks/useMigration';
import {describeError} from '../../lib/errors';
import {parametersOf} from '../../lib/parameters';
import {addToast} from '../../lib/toast';
import {
    cancelRun, deleteMigrations, executeMigration, reconfigureMigration, selectFiles, selectFolder,
} from '../../services/api';
import type {Bucket, Text} from '../../types';
import Alert from '../ui/Alert';
import ConfirmModal from '../ui/ConfirmModal';
import IconButton from '../ui/IconButton';
import Icon from '../ui/Icon';
import PageHeader from '../ui/PageHeader';
import Skeleton from '../ui/Skeleton';
import StatusBadge from '../ui/StatusBadge';
import ActionPanel from './ActionPanel';
import FilesPanel from './FilesPanel';
import FoldersPanel from './FoldersPanel';
import Notes from './Notes';
import StartModal from './StartModal';
import SummaryTiles, {type Tab} from './SummaryTiles';

interface ReviewProps {
    id: number;
    text: Text;
    onBack: () => void;
    /** Change the parameters of this migration, in the page that does it. */
    onEdit: () => void;
    /** Start another migration, from the parameters of this one. */
    onNew: () => void;
}

const Review = ({id, text, onBack, onEdit, onNew}: ReviewProps) => {
    const state = useMigration(id);
    const {migration, busy} = state;
    const [tab, setTab] = useState<Tab>('files');
    const [bucket, setBucket] = useState<Bucket>('');
    const [starting, setStarting] = useState(false);
    const [stopping, setStopping] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const parameters = useMemo(() => (migration ? parametersOf(migration) : null), [migration?.digest]);

    if (!migration || !parameters) {
        return <div className="fbm-review">
            <PageHeader
                title={text('review_title', {n: id})}
                back={<button type="button" className="btn btn-link btn-sm p-0 fbm-back" onClick={onBack}><Icon name="arrow-left" gap />{text('back_to_list')}</button>}
            />
            {state.error !== '' && <Alert tone="danger" live actions={<button type="button" className="btn btn-outline-secondary" onClick={() => void state.reload()}>{text('retry')}</button>}>
                {state.error}
            </Alert>}
            {state.loading && <Skeleton rows={6} label={text('loading')} />}
        </div>;
    }

    const current: Tab = tab;

    const start = async() => {
        const answer = await state.apply(() => executeMigration(migration.id, migration.digest));
        setStarting(false);
        if (answer) {
            void addToast(text(answer.run ? 'started' : 'nothing_started'), answer.run ? 'success' : 'info');
        }
    };

    const stop = async() => {
        if (migration.run) {
            await state.apply(() => cancelRun(migration.id, migration.run!.id));
        }
        setStopping(false);
    };

    const remove = async() => {
        try {
            await deleteMigrations([migration.id]);
            onBack();
        } catch (problem) {
            setDeleting(false);
            void addToast(describeError(problem), 'danger');
        }
    };

    return <div className="fbm-review" data-region="migration-review" data-migration={migration.id}>
        <PageHeader
            title={text('review_title', {n: migration.id})}
            badges={<StatusBadge status={migration.state} label={migration.statelabel} />}
            back={<button type="button" className="btn btn-link btn-sm p-0 fbm-back" onClick={onBack}><Icon name="arrow-left" gap />{text('back_to_list')}</button>}
            subtitle={<>
                <span className="fbm-route">
                    <strong>{migration.source}</strong><Icon name="arrow-right" /><strong>{migration.target}</strong>
                </span>
                {' · '}{migration.profile}{' · '}{migration.datelabel}
            </>}
            actions={<>
                {migration.editable && <button type="button" className="btn btn-outline-secondary" onClick={onEdit} disabled={busy} data-action="edit-parameters">
                    <Icon name="sliders" gap />{text('edit_parameters')}
                </button>}
                <button type="button" className="btn btn-outline-secondary" onClick={onNew} data-action="new-from">
                    <Icon name="plus" gap />{text('new_migration')}
                </button>
                {!migration.run?.active && <IconButton icon="trash" label={text('delete')} tone="danger" onClick={() => setDeleting(true)} />}
            </>}
        />
        {state.stale && <Alert tone="warning" live actions={<button type="button" className="btn btn-outline-secondary" onClick={() => void state.reload()}>{text('reload')}</button>}>
            {text('stale')}
        </Alert>}
        {state.error !== '' && !state.stale && <Alert tone="danger" live>{state.error}</Alert>}
        <SummaryTiles
            summary={migration.summary}
            text={text}
            active={{tab: current, bucket}}
            onTab={setTab}
            onFiles={(value) => {
                setBucket(value);
                setTab('files');
            }}
        />
        <ActionPanel
            migration={migration}
            text={text}
            busy={busy}
            onStart={() => setStarting(true)}
            onStop={() => setStopping(true)}
            onEdit={onEdit}
            onRework={() => void state.apply(() => reconfigureMigration(migration.id, migration.digest, parameters))}
        />
        <Notes notes={migration.summary.notes} text={text} />
        <div className="nav nav-tabs" role="tablist" aria-label={text('review_sections')}>
            {([
                ['files', 'file-lines', migration.summary.files],
                ['folders', 'folder-tree', migration.summary.folders],
            ] as Array<[Tab, string, number]>).map(([value, icon, count]) => <button
                key={value}
                type="button"
                role="tab"
                aria-selected={current === value}
                className={`nav-link${current === value ? ' active' : ''}`}
                onClick={() => setTab(value)}
                data-tab={value}
            >
                <Icon name={icon} gap />{text('tab_' + value)}<span className="badge rounded-pill fbm-count">{count}</span>
            </button>)}
        </div>
        {current === 'files' && <FilesPanel
            migration={migration}
            text={text}
            bucket={bucket}
            onBucket={setBucket}
            busy={busy}
            onSelect={(action, visible, included) => void state.apply(() => selectFiles(migration.id, migration.digest, action, visible, included))}
        />}
        {current === 'folders' && <FoldersPanel
            migration={migration}
            text={text}
            busy={busy}
            onSelect={(folder, included) => void state.apply(() => selectFolder(migration.id, migration.digest, folder, included))}
        />}
        <StartModal open={starting} migration={migration} text={text} busy={busy} onConfirm={() => void start()} onCancel={() => setStarting(false)} />
        <ConfirmModal
            open={stopping}
            tone="danger"
            icon="stop"
            busy={busy}
            title={text('stop_title')}
            confirmLabel={text('run_stop')}
            cancelLabel={text('cancel')}
            onConfirm={() => void stop()}
            onCancel={() => setStopping(false)}
        >
            <p>{text('stop_text')}</p>
        </ConfirmModal>
        <ConfirmModal
            open={deleting}
            tone="danger"
            icon="triangle-exclamation"
            title={text('delete_title')}
            confirmLabel={text('delete')}
            cancelLabel={text('cancel')}
            onConfirm={() => void remove()}
            onCancel={() => setDeleting(false)}
        >
            <p>{text('delete_text', {n: 1})}</p>
            <p className="text-muted">{text('delete_hint')}</p>
        </ConfirmModal>
    </div>;
};

export default Review;
