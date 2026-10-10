// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Initialize the feature actions and event listeners.
 *
 * @module local_coursefiles/local/review/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_coursefiles/local/review/selectors';
import {createActions} from 'local_coursefiles/local/review/actions';
import {registerListeners} from 'local_coursefiles/local/review/listeners';

/**
 * Enhance the native server-rendered view.
 */
export const init = () => {
    const form = document.querySelector(Selectors.reviewForm);
    if (!form) {
        return;
    }
    const actions = createActions(form);
    registerListeners(form, actions);
};
