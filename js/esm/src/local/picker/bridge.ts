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
 * @module local_filebank/local/picker/bridge
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import type {ApprovedFile} from '@moodle/lms/local_filebank/local/contracts';
import Selectors from '@moodle/lms/local_filebank/local/selectors';
import {eventElement} from '@moodle/lms/local_filebank/local/listeners';

type HostObject = HTMLObjectElement & {filebankPresentationCleanup?: () => void};



/**
 * Apply native host styling only while this embedded chooser owns its panel.
 */
export const presentation = () => {
    const browser = document.querySelector<HTMLFormElement>(Selectors.browser)!;
    if (browser) {
        browser.dataset.filebankReady = 'true';
    }
    if (window.parent === window) {
        return;
    }
    const parent = window.parent as Window & typeof globalThis;
    const object = [...parent.document.querySelectorAll<HostObject>(Selectors.hostObjects)]
        .find(element => element.contentDocument?.defaultView === window);
    const picker = object?.closest('.file-picker');
    if (!object || !picker) {
        return;
    }
    window.parent.require(['local_filebank/local/picker_host'], (adapter: {mount(object: HTMLObjectElement): void}) => adapter.mount(object));
    document.body.classList.add('filebank-embedded');
    const surface = picker.querySelector<HTMLElement>(Selectors.pickerSurface)!;
    const background = parent.getComputedStyle(surface).backgroundColor;
    if (background !== 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--filebank-host-background', background);
    }

};

/**
 * Submit an approved batch through Moodle's external chooser and native download contract.
 *
 * @param {Object} file Server-issued approval descriptor.
 */
export const complete = (file: ApprovedFile) => {
    const button = document.querySelector<HTMLButtonElement>(Selectors.confirm)!;
    const supported = window.parent !== window && window.parent.M?.core_filepicker?.select_file;
    if (button && supported) {
        button.disabled = false;
    }
    document.querySelector<HTMLFormElement>(Selectors.reviewForm)!?.addEventListener('input', event => {
        if (eventElement(event).matches(Selectors.reviewChecks)) {
            return;
        }
        if (button) {
            button.disabled = true;
        }
    });
    button?.addEventListener('click', () => {
        if (!supported) {
            return;
        }
        button.disabled = true;
        // The editor bridge runs in the owning document, which survives picker closure.
        if (file.editorid) {
            window.parent.require(['local_filebank/editor_import'], (bridge: {watch: (scope: string, editorid: string) => void}) => bridge.watch(file.scope, file.editorid));
        }
        window.parent.M!.core_filepicker!.select_file!(file);
        const confirmation = window.parent.document.querySelector<HTMLElement>(Selectors.nativeConfirmation)!;
        if (confirmation) {
            confirmation.click();
        } else {
            button.disabled = false;
        }
    });
};

