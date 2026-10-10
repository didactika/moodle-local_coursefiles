// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * form listeners.
 * @module local_filebank/local/form/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_filebank/local/form/selectors';
import {listen} from 'local_filebank/local/listeners';

/**
 * Delegated events also cover rows appended after initialization.
 *
 * @param {Object} actions Scoped actions created for this rendered feature.
 */
export const registerRows = (actions) => {
    const {wrapper} = actions;
    listen(wrapper, 'input', actions.synchronize);
    listen(wrapper, 'click', event => {
        const target = event.target;
        if (target.closest(Selectors.addRow)) {
            actions.addRow();
            return;
        }
        const remove = target.closest(Selectors.removeRow);
        if (remove) {
            actions.removeRow(remove.closest(Selectors.tableRow));
        }
    });
};

/**
 * Bind guided and advanced profile section visibility.
 *
 * @param {Object} actions Scoped actions created for this rendered feature.
 */
export const registerProfile = (actions) => {
    listen(actions.mode, 'change', actions.update);
};
