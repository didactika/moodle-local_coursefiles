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
import EmptyState from '../ui/EmptyState';
import Icon from '../ui/Icon';
import Skeleton from '../ui/Skeleton';
import StatTile from '../ui/StatTile';

interface CheckResultProps {
    summary: Summary | null;
    loading: boolean;
    error: string;
    text: Text;
}

const CheckResult = ({summary, loading, error, text}: CheckResultProps) => {
    if (error !== '') {
        return <Alert tone="danger" live>{error}</Alert>;
    }
    if (loading || !summary) {
        return <Skeleton rows={3} label={text('loading')} />;
    }
    if (summary.files === 0) {
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
            <StatTile label={text('summary_conflicts')} value={summary.conflicts + summary.openissues} tone="info" icon="hand-pointer" />
        </div>
        <p className="fbm-muted fbm-check-scope">
            <Icon name="folder-tree" gap />{text('check_scope', {
                folders: summary.folders, from: summary.sourcecourses, to: summary.targetcourses,
            })}
        </p>
        {!summary.haswork && <Alert tone="info">{text('check_no_work')}</Alert>}
        {summary.conflicts + summary.openissues > 0 && <Alert tone="info">{text('check_decisions_later')}</Alert>}
        {summary.notes.length > 0 && <ul className="fbm-notes">
            {summary.notes.map((note) => <li key={note.kind}><Icon name="circle-info" gap />{note.label}</li>)}
        </ul>}
    </div>;
};

export default CheckResult;
