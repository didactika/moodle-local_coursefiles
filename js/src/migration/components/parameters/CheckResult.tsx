/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a migration with the chosen parameters would do, counted before anything is stored. It never says that something failed:
 * what does not match is listed as information and what needs a decision is only counted.
 *
 * @module     local_filebank/migration/components/parameters/CheckResult
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Summary, Text} from '../../types';
import Alert from '../ui/Alert';
import ChoiceCards from '../ui/ChoiceCards';
import EmptyState from '../ui/EmptyState';
import Icon from '../ui/Icon';
import Skeleton from '../ui/Skeleton';
import StatTile from '../ui/StatTile';

interface CheckResultProps {
    summary: Summary | null;
    loading: boolean;
    error: string;
    text: Text;
    /** What happens with a file that has a different one in the destination: skip or overwrite. */
    policy: string;
    onPolicy: (policy: string) => void;
}

const CheckResult = ({summary, loading, error, text, policy, onPolicy}: CheckResultProps) => {
    if (error !== '') {
        return <Alert tone="danger" live>{error}</Alert>;
    }
    if (!summary) {
        return <Skeleton rows={3} label={text('loading')} />;
    }
    if (summary.files === 0 && !loading) {
        return <EmptyState
            plain
            icon="folder-open"
            title={text('check_nothing_title')}
            text={text('check_nothing_text')}
        />;
    }
    return <div className="fbm-check-result" data-region="check-result">
        <div className="fbm-tiles">
            <StatTile label={text('summary_tocopy')} value={summary.tocopy} tone="success" icon="copy" />
            <StatTile label={text('summary_tooverwrite')} value={summary.tooverwrite} tone="warning" icon="pen-to-square" />
            <StatTile label={text('summary_existing')} value={summary.existing} tone="neutral" icon="equals" />
            <StatTile label={text('summary_left')} value={summary.skipped} tone="neutral" icon="hand" />
        </div>
        <p className="text-muted fbm-check-scope">
            <Icon name="folder-tree" gap />{text('check_scope', {
                folders: summary.folders, from: summary.sourcecourses, to: summary.targetcourses,
            })}
        </p>
        {summary.different > 0 && <section className="fbm-conflict" data-region="conflict-choice">
            <h4 className="h5">{text('check_conflict_title', {n: summary.different})}</h4>
            <p className="text-muted fbm-hint">{text('check_conflict_help')}</p>
            <ChoiceCards
                label={text('policy_conflictpolicy')}
                name="fbm-conflict-policy"
                value={policy === 'overwrite' ? 'overwrite' : 'skip'}
                onChange={onPolicy}
                choices={['skip', 'overwrite'].map((value) => ({
                    value,
                    label: text('policy_conflictpolicy_' + value),
                    hint: text('policy_conflictpolicy_' + value + '_hint'),
                }))}
            />
        </section>}
        {!summary.haswork && <Alert tone="warning">{text('check_no_work')}</Alert>}
        {summary.notes.length > 0 && <ul className="fbm-notes">
            {summary.notes.map((note) => <li key={note.kind}><Icon name="circle-info" gap />{note.label}</li>)}
        </ul>}
    </div>;
};

export default CheckResult;
