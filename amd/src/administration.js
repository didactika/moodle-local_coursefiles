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
 * Native list selection, confirmation and shared pagination controls.
 * @module local_multipicker/administration
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Notification from 'core/notification';
import {getStrings} from 'core/str';

/**
 * Bind native bulk forms; live migration review has its own serialized adapter.
 */
export const init = () => {
    document.querySelectorAll('[data-multipicker-profiles],[data-multipicker-migrations]').forEach(form => {
        if (form.dataset.ready) {
            return;
        }
        form.dataset.ready = 'true';
        const all = form.querySelector('[data-multipicker-selectall]');
        const checks = [...form.querySelectorAll('input[name="profiles[]"],input[name="plans[]"]')];
        const update = () => {
            const count = checks.filter(check => check.checked).length;
            if (all) {
                all.checked = checks.length > 0 && count === checks.length;
                all.indeterminate = count > 0 && count < checks.length;
            }
            const bulk = form.querySelector('[data-multipicker-bulk-actions]');
            if (bulk) {
                bulk.hidden = count === 0;
                bulk.querySelectorAll('button').forEach(button => {
                    button.disabled = count === 0;
                });
            }
        };
        all?.addEventListener('change', () => {
            checks.forEach(check => {
                check.checked = all.checked;
            });
            update();
        });
        checks.forEach(check => check.addEventListener('change', update));
        form.addEventListener('submit', async event => {
            const button = event.submitter;
            const destructive = button && (button.value === 'delete' || button.name === 'deleteplan'
                || button.value.startsWith('delete:'));
            if (!destructive || form.dataset.confirmed) {
                delete form.dataset.confirmed;
                return;
            }
            event.preventDefault();
            try {
                const keys = ['confirm', form.hasAttribute('data-multipicker-migrations')
                    ? 'migrationdeleteconfirm' : 'profilestateconfirm', 'delete', 'cancel'];
                const [title, message, yes, no] = await getStrings(keys.map(key => ({
                    key, component: key.endsWith('confirm') && key !== 'confirm' ? 'local_multipicker' : 'core',
                })));
                Notification.confirm(title, message, yes, no, () => {
                    form.dataset.confirmed = 'true';
                    form.requestSubmit(button);
                });
            } catch (error) {
                Notification.exception(error);
            }
        });
        update();
    });
    if (!document.body.dataset.multipickerPagination) {
        document.body.dataset.multipickerPagination = 'true';
        document.addEventListener('change', event => {
            const select = event.target.closest('[data-multipicker-pagesize]');
            if (!select || select.closest('[data-region=multipicker-migration]')) {
                return;
            }
            const url = new URL(select.dataset.url, document.baseURI);
            url.searchParams.set('perpage', select.value);
            url.searchParams.set(select.dataset.pageparameter, '0');
            window.location.assign(url);
        });
    }
};
