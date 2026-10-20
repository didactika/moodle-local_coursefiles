/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question before something that matters, in the save and cancel modal of Moodle: its header, its buttons and its look are the
 * ones of the theme, and only its body is drawn here. Cancel, its close button and Escape answer no.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState, type ReactNode} from 'react';
import {createPortal} from 'react-dom';
import {requireAsync} from '@moodle/lms/core/amd';

/** The part of a modal of Moodle that is used here. */
interface MoodleModal {
    getRoot(): {0: HTMLElement; on(name: string, handler: (event: Event) => void): void};
    getBody(): {0: HTMLElement};
    setSaveButtonText(text: string): void;
    show(): void;
    hide(): void;
    destroy(): void;
}

/** The save and cancel modal of Moodle. */
interface ModalFactory {
    create(options: {title: string; body: string; removeOnClose: boolean}): Promise<MoodleModal>;
}

/** The names of the events of a modal of Moodle. */
interface ModalEvents {
    save: string;
    hidden: string;
}

/**
 * The module itself, whether it comes as it is or as the default export of an ES module.
 *
 * @param module What was loaded.
 * @returns The module.
 */
const unwrap = <T, >(module: T | {default: T}): T => (module as {default?: T}).default ?? (module as T);

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
    /** Something is being done: the buttons wait. */
    busy?: boolean;
}

const ConfirmModal = ({
    open, title, children, confirmLabel, cancelLabel, onConfirm, onCancel, tone = 'primary', busy = false,
}: ConfirmModalProps) => {
    const [body, setBody] = useState<HTMLElement | null>(null);
    const current = useRef<MoodleModal | null>(null);
    const answers = useRef({onConfirm, onCancel});
    answers.current = {onConfirm, onCancel};

    useEffect(() => {
        if (!open) {
            return undefined;
        }
        let alive = true;
        let made: MoodleModal | null = null;
        void (async() => {
            const [factory, events] = await Promise.all([
                requireAsync<ModalFactory | {default: ModalFactory}>('core/modal_save_cancel').then(unwrap),
                requireAsync<ModalEvents | {default: ModalEvents}>('core/modal_events').then(unwrap),
            ]);
            const modal = await factory.create({title, body: '', removeOnClose: true});
            if (!alive) {
                modal.destroy();
                return;
            }
            made = modal;
            current.current = modal;
            modal.setSaveButtonText(confirmLabel);
            const root = modal.getRoot();
            const save = root[0].querySelector('[data-action="save"]');
            if (save && tone !== 'primary') {
                save.classList.replace('btn-primary', `btn-${tone}`);
            }
            const cancel = root[0].querySelector('[data-action="cancel"]');
            if (cancel) {
                cancel.textContent = cancelLabel;
            }
            root.on(events.save, (event) => {
                // The modal stays open while the answer is carried out; whoever asked closes it.
                event.preventDefault();
                answers.current.onConfirm();
            });
            root.on(events.hidden, () => {
                if (current.current === modal) {
                    current.current = null;
                    answers.current.onCancel();
                }
            });
            modal.show();
            setBody(modal.getBody()[0]);
        })();
        return () => {
            alive = false;
            setBody(null);
            if (made && current.current === made) {
                // Hiding it lets Moodle take away its backdrop; removeOnClose then destroys it.
                current.current = null;
                made.hide();
            }
        };
    }, [open]);

    useEffect(() => {
        body?.closest('.modal-content')?.querySelectorAll<HTMLButtonElement>('.modal-footer button, .modal-header button')
            .forEach((button) => {
                button.disabled = busy;
            });
    }, [busy, body]);

    return body ? createPortal(<div className="filebank-ui fbm-confirm-body">{children}</div>, body) : null;
};

export default ConfirmModal;
