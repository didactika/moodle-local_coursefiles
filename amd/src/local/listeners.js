// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Native listener registration shared by the feature controllers.
 *
 * @module local_filebank/local/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register a controller handler on its owning native DOM node.
 *
 * @param {EventTarget} root Owning node, document or window.
 * @param {string} type Native event name.
 * @param {Function} handler Controller handler retaining its local state.
 * @param {Object|boolean} options Native listener options.
 */
export const listen = (root, type, handler, options) => root.addEventListener(type, handler, options);
