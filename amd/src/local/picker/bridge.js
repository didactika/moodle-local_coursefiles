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
 * @module local_coursefiles/local/picker/bridge
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'local_coursefiles/local/selectors';
import {listen} from 'local_coursefiles/local/listeners';

let hostObject;
let hostObserver;

/**
 * Apply native host styling only while this embedded chooser owns its panel.
 */
export const presentation = () => {
    const browser = document.querySelector(Selectors.browser);
    if (browser) {
        browser.dataset.coursefilesReady = 'true';
    }
    if (window.parent === window) {
        return;
    }
    const object = [...window.parent.document.querySelectorAll(Selectors.hostObjects)]
        .find(element => element.contentDocument?.defaultView === window);
    const picker = object?.closest('.file-picker');
    if (!picker || hostObject === object) {
        return;
    }
    picker.classList.add('coursefiles-native-host');
    document.body.classList.add('coursefiles-embedded');
    const surface = picker.querySelector(Selectors.pickerSurface);
    const background = window.parent.getComputedStyle(surface).backgroundColor;
    if (background !== 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--coursefiles-host-background', background);
    }
    const repositories = picker.querySelector(Selectors.repositories);
    const active = repositories?.querySelector('.fp-repo.active');
    const revealRepository = () => {
        if (active && window.parent.innerWidth < 576) {
            repositories.scrollLeft = active.offsetLeft - repositories.offsetLeft;
        }
    };
    revealRepository();
    listen(window.parent, 'resize', revealRepository);
    // Keep styling across browser/review navigation, but release it when another repository takes over.
    hostObserver?.disconnect();
    hostObject = object;
    hostObserver = new MutationObserver(() => {
        if (!object.isConnected) {
            picker.classList.remove('coursefiles-native-host');
            hostObserver.disconnect();
            hostObject = null;
            window.parent.removeEventListener('resize', revealRepository);
        }
    });
    hostObserver.observe(picker, {childList: true, subtree: true});
    listen(window, 'pagehide', () => {
        if (!object.isConnected) {
            picker.classList.remove('coursefiles-native-host');
        }
        hostObserver.disconnect();
        window.parent.removeEventListener('resize', revealRepository);
    }, {once: true});
};

/**
 * Submit an approved batch through Moodle's external chooser and native download contract.
 *
 * @param {Object} file Server-issued approval descriptor.
 */
export const complete = file => {
    const button = document.querySelector(Selectors.confirm);
    const supported = window.parent !== window && window.parent.M?.core_filepicker?.select_file;
    if (button && supported) {
        button.disabled = false;
    }
    document.querySelector(Selectors.reviewForm)?.addEventListener('input', event => {
        if (event.target.matches(Selectors.reviewChecks)) {
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
            window.parent.require(['local_coursefiles/editor_import'], bridge => bridge.watch(file.scope, file.editorid));
        }
        window.parent.M.core_filepicker.select_file(file);
        const confirmation = window.parent.document.querySelector(Selectors.nativeConfirmation);
        if (confirmation) {
            confirmation.click();
        } else {
            button.disabled = false;
        }
    });
};

