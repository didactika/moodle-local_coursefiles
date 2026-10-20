/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The figures of a migration at the top of its page: what would be copied, overwritten or is already there, what waits for a
 * decision and how many folders it covers. Each one leads to the part of the page that lists what it counts.
 *
 * @module     local_filebank/migration/components/review/SummaryTiles
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Bucket, Summary, Text} from '../../types';
import StatTile from '../ui/StatTile';

export type Tab = 'files' | 'folders';

interface SummaryTilesProps {
    summary: Summary;
    text: Text;
    onFiles: (bucket: Bucket) => void;
    onTab: (tab: Tab) => void;
    active: {tab: Tab; bucket: Bucket};
}

const SummaryTiles = ({summary, text, onFiles, onTab, active}: SummaryTilesProps) => {
    const onFilesTab = active.tab === 'files';
    return <div className="fbm-tiles" data-region="summary">
        <StatTile
            label={text('summary_tocopy')}
            value={summary.tocopy}
            tone="success"
            icon="copy"
            hint={text('summary_tocopy_hint')}
            active={onFilesTab && active.bucket === 'copy'}
            onClick={() => onFiles('copy')}
        />
        <StatTile
            label={text('summary_tooverwrite')}
            value={summary.tooverwrite}
            tone="warning"
            icon="pen-to-square"
            hint={text('summary_tooverwrite_hint')}
            active={false}
            onClick={() => onFiles('copy')}
        />
        <StatTile
            label={text('summary_existing')}
            value={summary.existing}
            tone="neutral"
            icon="equals"
            hint={text('summary_existing_hint')}
            active={onFilesTab && active.bucket === 'existing'}
            onClick={() => onFiles('existing')}
        />
        <StatTile
            label={text('summary_left')}
            value={summary.skipped}
            tone="neutral"
            icon="hand"
            hint={text('summary_left_hint')}
            active={onFilesTab && active.bucket === 'left'}
            onClick={() => onFiles('left')}
        />
        <StatTile
            label={text('summary_folders')}
            value={summary.folders}
            tone="primary"
            icon="folder-tree"
            hint={text('summary_folders_hint', {from: summary.sourcecourses, to: summary.targetcourses})}
            active={active.tab === 'folders'}
            onClick={() => onTab('folders')}
        />
    </div>;
};

export default SummaryTiles;
