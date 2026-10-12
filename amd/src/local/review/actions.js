// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Rename reviewed files through Moodle's native save/cancel modal.
 *
 * @module local_filebank/local/review/actions
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_filebank/local/review/selectors';
import {createSaveCancel} from 'local_filebank/local/modal';
import ModalEvents from 'core/modal_events';
import {getString} from 'local_filebank/local/strings';
import Templates from 'core/templates';
import Notification from 'core/notification';

/**
 * Bind pencil actions without making every filename an editable field.
 * @param {HTMLFormElement} form Owning native form.
 * @returns {Object} Scoped review and rename actions.
 */
export const createActions = (form) => {
    const update = () => {
        const selected = [...form.querySelectorAll(Selectors.removeFiles)].filter(check => check.checked);
        form.querySelectorAll(Selectors.reviewFolder).forEach(check => {
            const descendants = [...check.closest(Selectors.details).querySelectorAll(Selectors.removeFiles)];
            const count = descendants.filter(file => file.checked).length;
            check.checked = count > 0 && count === descendants.length;
            check.indeterminate = count > 0 && count < descendants.length;
        });
        const remove = document.querySelector(Selectors.removeSelected);
        remove.hidden = selected.length === 0;
        remove.disabled = selected.length === 0;
    };
    const selectionChange = event => {
        const folder = event.target.closest(Selectors.reviewFolder);
        if (folder) {
            folder.closest(Selectors.details).querySelectorAll(Selectors.removeFiles).forEach(check => {
                check.checked = folder.checked;
            });
        }
        update();
    };
    const selectFile = event => {
        const row = event.target.closest(Selectors.reviewFile);
        if (!row || event.target.closest(Selectors.interactive)) {
            return;
        }
        const check = row.querySelector(Selectors.checkbox);
        check.checked = !check.checked;
        check.dispatchEvent(new Event('change', {bubbles: true}));
    };
    const renameFile = async event => {
        const button = event.target.closest(Selectors.rename);
        if (!button || button.disabled) {
            return;
        }
        button.disabled = true;
        try {
            const name = button.closest(Selectors.reviewFile).querySelector(Selectors.renameField);
            const title = await getString('renamefile', 'local_filebank');
            const {html, js} = await Templates.renderForPromise('local_filebank/local/selection/rename', {
                name: name.value, label: title,
            });
            const modal = await createSaveCancel(title, {html, js});
            modal.getRoot().on(ModalEvents.save, event => {
                const input = modal.getBody()[0].querySelector(Selectors.filename);
                if (!input.reportValidity()) {
                    event.preventDefault();
                    return;
                }
                name.value = input.value;
                form.requestSubmit();
            });
            modal.getRoot().on(ModalEvents.hidden, () => {
                button.disabled = false;
                button.focus({preventScroll: true});
            });
        } catch (error) {
            button.disabled = false;
            Notification.exception(error);
        }
    };
    update();
    return {selectionChange, selectFile, renameFile};
};
