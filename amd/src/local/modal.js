// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Native Save/Cancel modal compatibility for the shared maintenance line.
 *
 * @module local_filebank/local/modal
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';

/**
 * Use current modal construction or the original factory on older cores.
 * @param {string} title Modal title.
 * @param {Object} body Native rendered template with html and js.
 * @returns {Promise<Object>} Visible native Save/Cancel modal.
 */
export const createSaveCancel = async(title, body) => {
    if (typeof ModalSaveCancel.create === 'function') {
        const modal = await ModalSaveCancel.create({title, show: false, removeOnClose: true});
        // The create() API predates setTemplateJS(); setBodyContent() exists on all supported cores.
        await modal.setBodyContent(Promise.resolve(body));
        modal.show();
        return modal;
    }
    // Load the legacy factory only on cores that require it.
    const factory = await new Promise((resolve, reject) => require(['core/modal_factory'], resolve, reject));
    const modal = await factory.create({type: factory.types.SAVE_CANCEL, title, removeOnClose: true});
    await modal.setBodyContent(Promise.resolve(body));
    modal.show();
    return modal;
};
