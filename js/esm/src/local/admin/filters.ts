/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Filters that apply as they are set: a list, a date or a choice applies at once and what is typed does it a moment after the
 * last key, so there is no button to press. A form that names its list replaces only that list, so the page does not move and
 * the cursor stays where it is; any other form sends the page, which comes back with the cursor where it was. A drop-down of
 * filters closes when the person clicks outside it or presses Escape.
 *
 * @module local_filebank/local/admin/filters
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {swapList} from '@moodle/lms/local_filebank/local/admin/lists';

const DELAY = 400;
const FOCUS = 'local_filebank/live-focus';

/**
 * Read and forget the field that had the cursor when the page was sent.
 *
 * @returns The name of the field, or an empty string.
 */
const takeFocus = (): string => {
    try {
        const name = window.sessionStorage.getItem(FOCUS) ?? '';
        window.sessionStorage.removeItem(FOCUS);
        return name;
    } catch {
        return '';
    }
};

/**
 * Make the filter forms of the page apply as they are set.
 */
export const registerLiveFilters = (): void => {
    const focused = takeFocus();
    document.querySelectorAll<HTMLFormElement>('form[data-filebank-live-filters]').forEach(form => {
        if (form.dataset.ready) {
            return;
        }
        form.dataset.ready = 'true';
        let timer = 0;
        const send = () => {
            window.clearTimeout(timer);
            const list = form.dataset.filebankTarget
                ? document.querySelector<HTMLElement>(`[data-filebank-list="${form.dataset.filebankTarget}"]`) : null;
            if (list) {
                // The attribute, not the property: a field named "action" (the log has one) would shadow it.
                const url = new URL(form.getAttribute('action') || '', window.location.href);
                const params = new URLSearchParams();
                new FormData(form).forEach((value, name) => {
                    if (typeof value === 'string') {
                        params.append(name, value);
                    }
                });
                url.search = params.toString();
                void swapList(list, url.toString());
                return;
            }
            const active = document.activeElement;
            if (active instanceof HTMLInputElement && form.contains(active) && active.name !== '') {
                try {
                    window.sessionStorage.setItem(FOCUS, active.name);
                } catch {
                    // Without storage the page simply comes back without the cursor in the field.
                }
            }
            form.requestSubmit();
        };
        form.addEventListener('change', send);
        form.addEventListener('input', event => {
            if (event.target instanceof HTMLElement && event.target.matches('[data-filebank-live-text]')) {
                window.clearTimeout(timer);
                timer = window.setTimeout(send, DELAY);
            }
        });
        const field = focused !== '' ? form.querySelector<HTMLInputElement>(`input[name="${CSS.escape(focused)}"]`) : null;
        if (field) {
            field.focus();
            field.setSelectionRange(field.value.length, field.value.length);
        }
    });
};

/**
 * Close the open drop-downs of filters when the person clicks outside them or presses Escape.
 */
export const registerFilterMenus = (): void => {
    if (document.body.dataset.filebankMenus) {
        return;
    }
    document.body.dataset.filebankMenus = 'true';
    const close = (keep: EventTarget | null) => {
        document.querySelectorAll<HTMLDetailsElement>('details.fbm-filterbox[open]').forEach(box => {
            if (!(keep instanceof Node) || !box.contains(keep)) {
                box.open = false;
            }
        });
    };
    document.addEventListener('click', event => {
        // The calendar of a date is drawn at the end of the page, outside the drop-down that asked for it.
        if (event.target instanceof Element && event.target.closest('.daterangepicker')) {
            return;
        }
        close(event.target);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            close(null);
        }
    });
};
