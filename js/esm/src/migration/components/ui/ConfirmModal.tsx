/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question that must be answered before something that is hard to undo, in a dialog that takes the focus and closes with Escape.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';
import Modal from './Modal';

interface ConfirmModalProps {
    open: boolean;
    title: string;
    children: ReactNode;
    confirmLabel: string;
    cancelLabel: string;
    onConfirm: () => void;
    onCancel: () => void;
    /** Colour of the button that confirms. */
    tone?: 'primary' | 'danger' | 'warning';
    icon?: string;
    /** Something is being done: the buttons wait. */
    busy?: boolean;
}

const ConfirmModal = ({
    open, title, children, confirmLabel, cancelLabel, onConfirm, onCancel, tone = 'primary', icon = 'circle-question', busy = false,
}: ConfirmModalProps) => <Modal
    open={open}
    size="small"
    busy={busy}
    title={<><Icon name={icon} gap />{title}</>}
    onClose={onCancel}
    closeLabel={cancelLabel}
    footer={<>
        <button type="button" className="btn btn-outline-secondary" onClick={onCancel} disabled={busy}>{cancelLabel}</button>
        <button type="button" className={`btn btn-${tone}`} onClick={onConfirm} disabled={busy}>
            {busy && <Icon name="spinner" gap spin />}{confirmLabel}
        </button>
    </>}
>
    <div className="fbm-confirm-body">{children}</div>
</Modal>;

export default ConfirmModal;
