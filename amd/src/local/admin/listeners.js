// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * admin listeners.
 * @module local_multipicker/local/admin/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_multipicker/local/admin/selectors';
import {listen} from 'local_multipicker/local/listeners';
import {changePage} from 'local_multipicker/local/admin/actions';

/**
 * Bind a bulk form while preserving selection after saves.
 *
 * @param {HTMLFormElement} form Owning native form.
 * @param {Object} actions Scoped actions created for this rendered feature.
 */
export const registerBulk = (form, actions) => {
    const all = form.querySelector(Selectors.selectAll);
    if (all) {
        listen(all, 'change', actions.selectAllChange);
    }
    form.querySelectorAll(Selectors.bulkChecks).forEach(check => listen(check, 'change', actions.update));
    listen(form, 'submit', actions.submit);
};

/**
 * Bind version comparison controls.
 *
 * @param {HTMLElement} history Version history container.
 * @param {Object} actions Scoped actions created for this rendered feature.
 */
export const registerHistory = (history, actions) => {
    history.querySelectorAll(Selectors.versions).forEach(check => listen(check, 'change', actions.update));
};

/**
 * Register the delegated pagination listener once.
 */
export const registerPagination = () => {
    if (!document.body.dataset.multipickerPagination) {
        document.body.dataset.multipickerPagination = 'true';
        listen(document, 'change', changePage);
    }
};
