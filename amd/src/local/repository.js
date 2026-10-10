// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Authorized fragment loading; UI controllers retain only their own view state.
 *
 * @module local_filebank/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from 'core/config';
import Fragment from 'core/fragment';

/**
 * Load one component fragment through Moodle's session-aware API.
 *
 * @param {string} name Supported fragment name.
 * @param {number} contextid Owning context.
 * @param {Object} args Bounded fragment arguments.
 * @returns {Promise<Object>} Rendered HTML and template JavaScript.
 */
export const loadFragment = (name, contextid, args) => new Promise((resolve, reject) => {
    Fragment.loadFragment('local_filebank', name, contextid, args)
        .then((html, js) => resolve({html, js: js || ''}), reject);
});

/**
 * Read the owning editor's session-bound completion receipt.
 *
 * @param {string} scope Native editor scope.
 * @returns {Promise<Object|null>} Approved receipt, or null when unavailable.
 */
export const getImportStatus = async(scope) => {
    const url = Config.wwwroot + '/local/filebank/selection/status.php?'
        + new URLSearchParams({scope, sesskey: Config.sesskey});
    const response = await fetch(url, {credentials: 'same-origin'});
    return response.ok ? await response.json() : null;
};
