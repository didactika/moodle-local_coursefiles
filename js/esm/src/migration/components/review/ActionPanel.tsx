/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the person can do with the migration now, said in one place: start it, change its parameters because there is nothing to
 * write, work it out again because the profile changed, follow it while it runs, or read how it ended.
 *
 * @module     local_filebank/migration/components/review/ActionPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Migration, Text} from '../../types';
import Alert from '../ui/Alert';
import Icon from '../ui/Icon';
import Progress from '../ui/Progress';

interface ActionPanelProps {
    migration: Migration;
    text: Text;
    busy: boolean;
    onStart: () => void;
    onStop: () => void;
    onEdit: () => void;
    onRework: () => void;
}

const ActionPanel = ({migration, text, busy, onStart, onStop, onEdit, onRework}: ActionPanelProps) => {
    const {run, summary} = migration;

    if (run?.active) {
        return <div className="fbm-action is-running" data-region="run">
            <div className="fbm-action-body">
                <strong>{text('run_running', {done: run.done, total: run.total})}</strong>
                <Progress value={run.done} max={Math.max(1, run.total)} label={text('progress')} />
                <span className="text-muted">{text('run_running_hint')}</span>
            </div>
            {run.cancancel && <button type="button" className="btn btn-danger" onClick={onStop} disabled={busy}>
                <Icon name="stop" gap />{text('run_stop')}
            </button>}
        </div>;
    }
    if (run) {
        const tone = run.state === 'completed' ? 'success' : run.state === 'cancelled' ? 'neutral' : 'warning';
        return <Alert tone={tone}>
            <strong>{text('run_' + run.state)}</strong>{' '}
            <span>{text('run_done', {done: run.done, total: run.total})}</span>
            {run.state === 'partial' && <span> {text('run_partial_hint')}</span>}
        </Alert>;
    }
    if (migration.outdated) {
        return <Alert
            tone="warning"
            actions={<button type="button" className="btn btn-primary" onClick={onRework} disabled={busy}>
                <Icon name="rotate" gap />{text('outdated_rework')}
            </button>}
        >
            <strong>{text('outdated_title')}</strong> {text('outdated_text')}
        </Alert>;
    }
    if (!summary.haswork) {
        return <Alert
            tone="info"
            actions={migration.editable && <button type="button" className="btn btn-primary" onClick={onEdit} disabled={busy}>
                <Icon name="sliders" gap />{text('edit_parameters')}
            </button>}
        >
            <strong>{text('nothing_title')}</strong> {text('nothing_text')}
        </Alert>;
    }
    return <div className="fbm-action is-ready" data-region="start">
        <div className="fbm-action-body">
            <strong>{text('start_ready', {n: summary.tocopy + summary.tooverwrite})}</strong>
            <span className="text-muted">{text('start_all_set')}</span>
        </div>
        {migration.canexecute
            ? <button type="button" className="btn btn-success btn-lg" onClick={onStart} disabled={busy} data-action="start">
                <Icon name="play" gap />{text('start')}
            </button>
            : <span className="text-muted">{text('start_no_permission')}</span>}
    </div>;
};

export default ActionPanel;
