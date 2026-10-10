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
 * @module local_multipicker/review
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import {getString} from 'core/str';
import Templates from 'core/templates';
import Notification from 'core/notification';

/**
 * Bind pencil actions without making every filename an editable field.
 */
export const init = () => {
    const form = document.querySelector('.multipicker-review-form');
    if (!form) {
        return;
    }
    const update = () => {
        const selected = [...form.querySelectorAll('input[name="removefiles[]"]')].filter(check => check.checked);
        form.querySelectorAll('[data-multipicker-review-folder]').forEach(check => {
            const descendants = [...check.closest('details').querySelectorAll('input[name="removefiles[]"]')];
            const count = descendants.filter(file => file.checked).length;
            check.checked = count > 0 && count === descendants.length;
            check.indeterminate = count > 0 && count < descendants.length;
        });
        const remove = document.querySelector('[data-multipicker-remove-selected]');
        remove.hidden = selected.length === 0;
        remove.disabled = selected.length === 0;
    };
    form.addEventListener('change', event => {
        const folder = event.target.closest('[data-multipicker-review-folder]');
        if (folder) {
            folder.closest('details').querySelectorAll('input[name="removefiles[]"]').forEach(check => {
                check.checked = folder.checked;
            });
        }
        update();
    });
    form.addEventListener('click', event => {
        const row = event.target.closest('.multipicker-tree-file');
        if (!row || event.target.closest('button, a, input, label')) {
            return;
        }
        const check = row.querySelector('input[type=checkbox]');
        check.checked = !check.checked;
        check.dispatchEvent(new Event('change', {bubbles: true}));
    });
    form.addEventListener('click', async event => {
        const button = event.target.closest('[data-multipicker-rename]');
        if (!button || button.disabled) {
            return;
        }
        button.disabled = true;
        try {
            const name = button.closest('.multipicker-tree-file').querySelector('input[name^=renames]');
            const title = await getString('renamefile', 'local_multipicker');
            const {html, js} = await Templates.renderForPromise('local_multipicker/local/selection/rename', {
                name: name.value, label: title,
            });
            const modal = await ModalSaveCancel.create({title, body: html, show: true, removeOnClose: true});
            modal.setTemplateJS(js);
            modal.getRoot().on(ModalEvents.save, event => {
                const input = modal.getBody()[0].querySelector('[data-multipicker-filename]');
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
    });
    update();
};
