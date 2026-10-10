// This file is part of Moodle - https://moodle.org/
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
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_coursefiles/local/editor/actions
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_coursefiles/local/selectors';


/**
 * Append approved links after Moodle inserts the first one.
 *
 * @param {Object} editor Native Tiny instance.
 * @param {Object} result Approved actor-bound import receipt.
 * @returns {boolean} Whether the native callback is ready.
 */
export const appendLinks = (editor, result) => {
    // Await the core callback, so it retains its selected text, focus and first-link behavior.
    const linked = new Set([...editor.getBody().querySelectorAll(Selectors.links)].map(link => link.href));
    if (result.files.some(file => linked.has(file.url))) {
        const list = document.createElement('ul');
        result.files.filter(file => !linked.has(file.url)).forEach(file => {
            const item = document.createElement('li');
            const anchor = document.createElement('a');
            anchor.href = file.url;
            anchor.textContent = file.name;
            item.append(anchor);
            list.append(item);
        });
        if (list.children.length) {
            editor.undoManager.transact(() => {
                editor.selection.collapse(false);
                editor.insertContent(list.outerHTML);
                editor.save();
            });
        }
        return true;
    }
    return false;
};
