/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A window over the page, in a dialog of the browser: it takes the focus, closes with Escape or with its button and gives the focus
 * back to where the person was.
 *
 * @module     local_filebank/migration/components/ui/Modal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, type ReactNode} from 'react';
import Icon from './Icon';

interface ModalProps {
    open: boolean;
    title: ReactNode;
    onClose: () => void;
    closeLabel: string;
    children: ReactNode;
    footer?: ReactNode;
    /** Width of the dialog: a short question, a form or a list. */
    size?: 'small' | 'medium' | 'large';
    /** Something that is being saved: the dialog cannot be closed until it ends. */
    busy?: boolean;
}

const Modal = ({open, title, onClose, closeLabel, children, footer, size = 'medium', busy = false}: ModalProps) => {
    const dialog = useRef<HTMLDialogElement>(null);

    useEffect(() => {
        const element = dialog.current;
        if (!element) {
            return;
        }
        if (open && !element.open) {
            element.showModal();
        } else if (!open && element.open) {
            element.close();
        }
    }, [open]);

    const close = () => {
        if (!busy) {
            onClose();
        }
    };

    return <dialog
        ref={dialog}
        className={`fbm-dialog fbm-dialog-${size}`}
        onCancel={(event) => {
            // Escape does not close it either: a window is closed with its own button, so nothing is lost by a stray key or click.
            event.preventDefault();
        }}
    >
        {open && <div className="fbm-dialog-body">
            <header className="fbm-dialog-head">
                <h3 className="fbm-dialog-title">{title}</h3>
                <button type="button" className="fbm-iconbtn" onClick={close} title={closeLabel} disabled={busy}>
                    <Icon name="xmark" /><span className="fbm-sr">{closeLabel}</span>
                </button>
            </header>
            <div className="fbm-dialog-scroll">{children}</div>
            {footer && <footer className="fbm-dialog-foot">{footer}</footer>}
        </div>}
    </dialog>;
};

export default Modal;
