// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Authenticated backend requests shared by the typed feature actions.
 *
 * @module local_coursefiles/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from '@moodle/lms/core/config';
import type {EditorImportStatus} from '@moodle/lms/local_coursefiles/local/contracts';
import Fragment from '@moodle/lms/local_coursefiles/compat/fragment';
import type {RenderedTemplate} from '@moodle/lms/local_coursefiles/compat/templates';

/**
 * Load an authorized fragment without exposing jQuery to the controllers.
 *
 * @param {string} name Supported fragment name.
 * @param {number} contextid Owning context.
 * @param {Object} args Bounded fragment arguments.
 */
export const loadFragment = (name: string, contextid: number, args: object): Promise<RenderedTemplate> =>
    new Promise((resolve, reject) => {
        Fragment.loadFragment('local_coursefiles', name, contextid, args)
            .then((html, js) => resolve({html, js: js || ''}), reject);
    });

/**
 * Read the owning editor's session-bound completion receipt.
 *
 * @param {string} scope Native editor scope.
 * @returns {Promise<Object|null>} Approved receipt, or null when unavailable.
 */
export const getImportStatus = async(scope: string): Promise<EditorImportStatus | null> => {
    const url = Config.wwwroot + '/local/coursefiles/selection/status.php?'
        + new URLSearchParams({scope, sesskey: Config.sesskey});
    const response = await fetch(url, {credentials: 'same-origin'});
    return response.ok ? await response.json() as EditorImportStatus : null;
};
