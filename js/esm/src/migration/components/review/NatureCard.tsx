/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One kind of thing that waits for a decision, with what can be done to all of it at once. Two hundred files that are in conflict
 * in the same way are settled with one click and one question, and can still be looked at one by one.
 *
 * @module     local_filebank/migration/components/review/NatureCard
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useState} from 'react';
import type {Nature, Text} from '../../types';
import ConfirmModal from '../ui/ConfirmModal';
import Icon from '../ui/Icon';
import DecisionList from './DecisionList';

interface NatureCardProps {
    migrationId: number;
    digest: string;
    nature: Nature;
    text: Text;
    busy: boolean;
    /** Settle some of it, or all of the nature when no ids are given. */
    onResolve: (nature: Nature, ids: number[], choice: string) => Promise<boolean>;
}

const NatureCard = ({migrationId, digest, nature, text, busy, onResolve}: NatureCardProps) => {
    const [open, setOpen] = useState(false);
    const [asking, setAsking] = useState('');
    const label = nature.choices.find((choice) => choice.value === asking)?.label ?? '';

    const confirm = async() => {
        const done = await onResolve(nature, [], asking);
        if (done) {
            setAsking('');
        }
    };

    return <section className="fbm-nature" data-nature={nature.nature}>
        <header className="fbm-nature-head">
            <span className="fbm-nature-icon"><Icon name={nature.kind === 'file' ? 'file-circle-exclamation' : 'folder-open'} /></span>
            <div className="fbm-nature-title">
                <h4 className="fbm-h4">{nature.label}<span className="fbm-count">{nature.count}</span></h4>
                <p className="fbm-muted fbm-nature-help">{text('nature_help_' + nature.kind)}</p>
            </div>
        </header>
        <div className="fbm-nature-actions">
            {nature.choices.map((choice) => <button
                key={choice.value}
                type="button"
                className={`fbm-btn ${choice.value === 'overwrite' ? 'fbm-btn-warning' : 'fbm-btn-primary'}`}
                disabled={busy}
                onClick={() => setAsking(choice.value)}
                data-choice={choice.value}
            >
                {text('apply_all', {n: nature.count})}: {choice.label}
            </button>)}
            <button type="button" className="fbm-btn" aria-expanded={open} onClick={() => setOpen(!open)}>
                <Icon name={open ? 'chevron-up' : 'list'} gap />{open ? text('hide_list') : text('review_one_by_one')}
            </button>
        </div>
        {open && <DecisionList
            migrationId={migrationId}
            digest={digest}
            nature={nature}
            text={text}
            busy={busy}
            onApply={(ids, choice) => void onResolve(nature, ids, choice)}
        />}
        <ConfirmModal
            open={asking !== ''}
            tone={asking === 'overwrite' ? 'warning' : 'primary'}
            icon={asking === 'overwrite' ? 'triangle-exclamation' : 'circle-question'}
            busy={busy}
            title={text('apply_all_title', {n: nature.count})}
            confirmLabel={text('apply')}
            cancelLabel={text('cancel')}
            onConfirm={() => void confirm()}
            onCancel={() => setAsking('')}
        >
            <p><strong>{nature.label}</strong></p>
            <p>{label}</p>
            <p className="fbm-muted">{text('apply_all_text', {n: nature.count})}</p>
        </ConfirmModal>
    </section>;
};

export default NatureCard;
