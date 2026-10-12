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
 * @module local_filebank/local/editor/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {appendLinks} from 'local_filebank/local/editor/actions';
import {getImportStatus} from 'local_filebank/local/repository';
import {resolve} from 'local_filebank/local/editor/adapter';
import Notification from 'core/notification';
import {getString} from 'local_filebank/local/strings';

/**
 * Insert remaining approved links after Tiny's native callback inserts the first one.
 *
 * @param {string} scope Session-local owning control.
 * @param {string} editorid Real server-rendered editor field ID.
 */
export const watch = async(scope, editorid) => {
    const until = Date.now() + 20000;
    try {
        while (Date.now() < until) {
            const result = await getImportStatus(scope);
            if (!result) {
                return;
            }
            if (result.ready) {
                if (!result.links || result.files.length < 2) {
                    return;
                }
                const editor = await resolve(editorid);
                if (!editor) {
                    Notification.alert('', await getString('editorimportfailed', 'local_filebank'));
                    return;
                }
                if (appendLinks(editor, result)) {
                    return;
                }
            }
            await new Promise(resolve => setTimeout(resolve, 200));
        }
    } catch (error) {
        Notification.exception(error);
    }
};
