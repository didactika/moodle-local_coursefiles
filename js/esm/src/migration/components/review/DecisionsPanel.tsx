/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Everything that waits for a decision in a migration, grouped by its nature. None of it stops the migration: what is not decided
 * stays out of it, and what is decided is saved in the same migration.
 *
 * @module     local_filebank/migration/components/review/DecisionsPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Migration, Nature, Text} from '../../types';
import EmptyState from '../ui/EmptyState';
import Panel from '../ui/Panel';
import NatureCard from './NatureCard';

interface DecisionsPanelProps {
    migration: Migration;
    text: Text;
    busy: boolean;
    onResolve: (nature: Nature, ids: number[], choice: string) => Promise<boolean>;
}

const DecisionsPanel = ({migration, text, busy, onResolve}: DecisionsPanelProps) => {
    const settled = migration.summary.skipped > 0;
    return <Panel title={text('tab_decisions')} icon="hand-pointer" count={migration.natures.reduce((sum, item) => sum + item.count, 0)} region="decisions">
        {migration.natures.length === 0
            ? <EmptyState plain icon="circle-check" title={text('decisions_none_title')} text={text(settled ? 'decisions_none_settled' : 'decisions_none_text')} />
            : <>
                <p className="fbm-muted fbm-panel-intro">{text('decisions_intro')}</p>
                <div className="fbm-natures">
                    {migration.natures.map((nature) => <NatureCard
                        key={nature.kind + nature.nature}
                        migrationId={migration.id}
                        digest={migration.digest}
                        nature={nature}
                        text={text}
                        busy={busy || !migration.editable}
                        onResolve={onResolve}
                    />)}
                </div>
            </>}
    </Panel>;
};

export default DecisionsPanel;
