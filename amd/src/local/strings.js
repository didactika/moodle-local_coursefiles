// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Native string API compatibility for the shared maintenance line.
 *
 * @module local_filebank/local/strings
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Str from 'core/str';

/**
 * Prefer the current API while retaining Moodle 4.0's original export.
 * @param {...*} args Native string arguments.
 * @returns {Promise<string>} Translated string.
 */
export const getString = (...args) => (Str.getString || Str.get_string)(...args);

/**
 * Resolve a batch using the API available in the current core.
 * @param {Object[]} requests Native string requests.
 * @returns {Promise<string[]>} Translated strings.
 */
export const getStrings = requests => (Str.getStrings || Str.get_strings)(requests);
