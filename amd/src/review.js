// This file is part of Moodle - http://moodle.org/
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
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
                form.requestSubmit(form.querySelector('[data-multipicker-save-review]'));
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
};
