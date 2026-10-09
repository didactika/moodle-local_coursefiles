// This file is part of Moodle - http://moodle.org/
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
 * @module local_coursebridge/browser
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add one native-styled entry beside the owning filemanager.
 *
 * @param {string} id Actual native field ID.
 * @param {number} repositoryid Installed repository instance.
 * @param {string} label Localized action label.
 */
export const attach = (id, repositoryid, label) => {
    const input = document.getElementById(id);
    const row = input?.closest('[id^="fitem_"]') || input?.parentElement;
    const manager = row?.querySelector('.filemanager');
    if (!manager || row.querySelector('[data-coursebridge-open]')) {
        return;
    }
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-secondary mb-2';
    button.dataset.coursebridgeOpen = '';
    button.textContent = label;
    manager.before(button);
    button.addEventListener('click', () => {
        const add = manager.querySelector('.fp-btn-add a');
        if (!add || add.closest('.fp-btn-add').classList.contains('hidden')) {
            return;
        }
        add.click();
        const observer = new MutationObserver(() => {
            const repository = document.querySelector(`[id^="fp-repo-"][id$="-${repositoryid}"]`);
            if (repository && repository.getClientRects().length) {
                observer.disconnect();
                (repository.querySelector('a') || repository).click();
            }
        });
        observer.observe(document.body, {childList: true, subtree: true, attributes: true});
        setTimeout(() => observer.disconnect(), 5000);
    });
};

/**
 * Submit an approved batch through Moodle's external chooser contract and native download action.
 *
 * @param {Object} file Server-issued approved source descriptor.
 */
export const complete = file => {
    const button = document.querySelector('[data-coursebridge-confirm]');
    if (button && window.parent !== window && window.parent.M?.core_filepicker?.select_file) {
        button.disabled = false;
    }
    button?.closest('form')?.addEventListener('input', () => {
        button.disabled = true;
    });
    button?.addEventListener('click', () => {
        if (window.parent === window || !window.parent.M?.core_filepicker?.select_file) {
            return;
        }
        button.disabled = true;
        window.parent.M.core_filepicker.select_file(file);
        const confirmation = window.parent.document.querySelector('.file-picker.fp-select .fp-select-confirm');
        if (confirmation) {
            confirmation.click();
        } else {
            button.disabled = false;
        }
    });
};

/** Update the visible count without granting authority to browser state. */
export const selection = () => {
    const form = document.querySelector('.coursebridge-browser');
    const update = () => {
        const count = form?.querySelector('[data-coursebridge-count]');
        if (count) {
            count.textContent = new Set([...form.querySelectorAll('input[name="items[]"]')]
                .filter(input => input.type === 'hidden' || input.checked).map(input => input.value)).size;
        }
    };
    form?.addEventListener('change', update);
    update();
};
