/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The last question before a migration writes anything: what it will do, with the files that overwrite another one said apart,
 * and what it will leave as it is.
 *
 * @module     local_filebank/migration/components/review/StartModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Migration, Text} from '../../types';
import Alert from '../ui/Alert';
import ConfirmModal from '../ui/ConfirmModal';

interface StartModalProps {
    open: boolean;
    migration: Migration;
    text: Text;
    busy: boolean;
    onConfirm: () => void;
    onCancel: () => void;
}

const StartModal = ({open, migration, text, busy, onConfirm, onCancel}: StartModalProps) => {
    const {summary} = migration;
    return <ConfirmModal
        open={open}
        icon="play"
        busy={busy}
        title={text('start_title', {n: migration.id})}
        confirmLabel={text('start')}
        cancelLabel={text('cancel')}
        onConfirm={onConfirm}
        onCancel={onCancel}
    >
        <ul className="fbm-confirm-list">
            <li><strong>{summary.tocopy}</strong> {text('start_copy')}</li>
            {summary.tooverwrite > 0 && <li className="is-warning"><strong>{summary.tooverwrite}</strong> {text('start_overwrite')}</li>}
            <li className="fbm-muted"><strong>{summary.existing}</strong> {text('start_existing')}</li>
        </ul>
        {summary.tooverwrite > 0 && <Alert tone="warning">{text('start_overwrite_warning')}</Alert>}
        {summary.conflicts + summary.openissues > 0 && <p className="fbm-muted">
            {text('start_left', {n: summary.conflicts + summary.openissues})}
        </p>}
        <p className="fbm-muted">{text('start_background')}</p>
    </ConfirmModal>;
};

export default StartModal;
