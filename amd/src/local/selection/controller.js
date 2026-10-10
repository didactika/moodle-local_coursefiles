// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Initialize the feature actions and event listeners.
 *
 * @module local_multipicker/local/selection/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_multipicker/local/selection/selectors';
import {createActions} from 'local_multipicker/local/selection/actions';
import {registerListeners} from 'local_multipicker/local/selection/listeners';
import {presentation} from 'local_multipicker/local/picker/bridge';

/**
 * Enhance the native server-rendered view.
 */
export const selection = () => {
    presentation();
    const form = document.querySelector(Selectors.selectionForm);
    if (!form) {
        return;
    }
    const actions = createActions(form);
    registerListeners(form, actions);
};
