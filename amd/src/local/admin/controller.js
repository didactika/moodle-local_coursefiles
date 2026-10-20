// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * admin controller.
 * @module local_filebank/local/admin/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_filebank/local/admin/selectors';
import {createBulkActions, createHistoryActions} from 'local_filebank/local/admin/actions';
import {registerBulk, registerHistory, registerPagination} from 'local_filebank/local/admin/listeners';
import {registerFilterMenus, registerLiveFilters} from 'local_filebank/local/admin/filters';
import {registerLists} from 'local_filebank/local/admin/lists';

/**
 * Initialize native administration controls.
 */
export const init = () => {
    document.querySelectorAll(Selectors.bulkForms).forEach(form => {
        const actions = createBulkActions(form);
        if (actions) {
            registerBulk(form, actions);
        }
    });
    document.querySelectorAll(Selectors.history).forEach(history => {
        const actions = createHistoryActions(history);
        if (actions) {
            registerHistory(history, actions);
        }
    });
    registerPagination();
    registerLiveFilters();
    registerFilterMenus();
    registerLists();
};
