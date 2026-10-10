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
 * @module local_multipicker/browser
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Notification from 'core/notification';

let hostObject;
let hostObserver;

/**
 * Apply native host styling only while this embedded chooser owns its panel.
 */
export const presentation = () => {
    const browser = document.querySelector('.multipicker-browser');
    if (browser) {
        browser.dataset.multipickerReady = 'true';
    }
    if (window.parent === window) {
        return;
    }
    const object = [...window.parent.document.querySelectorAll('.fp-object-container')]
        .find(element => element.contentDocument?.defaultView === window);
    const picker = object?.closest('.file-picker');
    if (!picker || hostObject === object) {
        return;
    }
    picker.classList.add('multipicker-native-host');
    document.body.classList.add('multipicker-embedded');
    const surface = picker.querySelector('.fp-content');
    const background = window.parent.getComputedStyle(surface).backgroundColor;
    if (background !== 'rgba(0, 0, 0, 0)') {
        document.body.style.setProperty('--multipicker-host-background', background);
    }
    const repositories = picker.querySelector('.fp-repo-area');
    const active = repositories?.querySelector('.fp-repo.active');
    const revealRepository = () => {
        if (active && window.parent.innerWidth < 576) {
            repositories.scrollLeft = active.offsetLeft - repositories.offsetLeft;
        }
    };
    revealRepository();
    window.parent.addEventListener('resize', revealRepository);
    // Keep styling across browser/review navigation, but release it when another repository takes over.
    hostObserver?.disconnect();
    hostObject = object;
    hostObserver = new MutationObserver(() => {
        if (!object.isConnected) {
            picker.classList.remove('multipicker-native-host');
            hostObserver.disconnect();
            hostObject = null;
            window.parent.removeEventListener('resize', revealRepository);
        }
    });
    hostObserver.observe(picker, {childList: true, subtree: true});
    window.addEventListener('pagehide', () => {
        if (!object.isConnected) {
            picker.classList.remove('multipicker-native-host');
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
    const button = document.querySelector('[data-multipicker-confirm]');
    const supported = window.parent !== window && window.parent.M?.core_filepicker?.select_file;
    if (button && supported) {
        button.disabled = false;
    }
    document.querySelector('.multipicker-review-form')?.addEventListener('input', () => {
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
            window.parent.require(['local_multipicker/editor_import'], bridge => bridge.watch(file.scope, file.editorid));
        }
        window.parent.M.core_filepicker.select_file(file);
        const confirmation = window.parent.document.querySelector('.file-picker.fp-select .fp-select-confirm');
        if (confirmation) {
            confirmation.click();
        } else {
            button.disabled = false;
        }
    });
};

/**
 * Keep selection visible, apply filters automatically and provide functional card/list layouts.
 */
export const selection = () => {
    presentation();
    const form = document.querySelector('form.multipicker-browser');
    if (!form) {
        return;
    }
    let timer;
    let submitting = false;
    let loading = false;
    let changes = 0;
    form.dataset.profileid = form.elements.profileid.value;
    form.querySelector('[data-multipicker-search]').hidden = true;
    const review = form.querySelector('[data-multipicker-review]');
    const update = () => {
        const count = new Set([...form.querySelectorAll('input[name="items[]"]')]
            .filter(input => input.type === 'hidden' || input.checked).map(input => input.value)).size;
        form.querySelector('[data-multipicker-count]').textContent = count;
        review.disabled = !count || loading;
        form.querySelectorAll('[data-multipicker-choice]').forEach(input => {
            input.indeterminate = input.dataset.partial === '1';
        });
        form.querySelectorAll('.multipicker-card').forEach(card => {
            card.classList.toggle('multipicker-selected', card.querySelector('input[type=checkbox]').checked);
        });
    };
    const apply = async(toggleitem = '') => {
        clearTimeout(timer);
        if (submitting || loading || !form.isConnected) {
            return;
        }
        loading = true;
        const version = changes;
        let retry = false;
        const raw = {items: [], captures: {}, toggleitem, refresh: true};
        for (const [name, value] of new FormData(form)) {
            if (name === 'items[]') {
                raw.items.push(value);
            } else if (name.startsWith('captures[')) {
                raw.captures[name.slice(9, -1)] = value;
            } else {
                raw[name] = value;
            }
        }
        form.setAttribute('aria-busy', 'true');
        form.querySelector('[data-multipicker-results]').classList.add('multipicker-loading');
        const actions = [...form.querySelectorAll('button[name], [data-multipicker-choice]')];
        actions.forEach(button => {
            button.dataset.multipickerDisabled = String(button.disabled);
            button.disabled = true;
        });
        getString('filterloading', 'local_multipicker').then(text => {
            form.querySelector('[data-multipicker-loading]').textContent = text;
            return text;
        }).catch(() => {});
        try {
            const {html} = await new Promise((resolve, reject) => {
                Fragment.loadFragment('local_multipicker', 'browser', Number(form.dataset.contextid), {
                    scope: raw.scope, state: JSON.stringify(raw),
                }).then((html, js) => resolve({html, js}), reject);
            });
            if (changes !== version && !toggleitem) {
                retry = true;
                return;
            }
            const response = new DOMParser().parseFromString(html, 'text/html');
            const next = response.querySelector('form.multipicker-browser');
            const results = form.querySelector('[data-multipicker-results]');
            const position = results.scrollTop;
            if (changes === version) {
                await Templates.replaceNodeContents(results, next.querySelector('[data-multipicker-results]').innerHTML, '');
            }
            results.scrollTop = position;
            // Keep active inputs, caret and native select menus in their original DOM nodes.
            form.querySelectorAll('input[name="items[]"]').forEach(input => input.remove());
            next.querySelectorAll('input[name="items[]"]').forEach(input => form.prepend(input));
            form.querySelector('[data-multipicker-count]').textContent =
                next.querySelector('[data-multipicker-count]').textContent;
            if (changes !== version) {
                retry = true;
                toggleitem = '';
            }
            const profileChanged = form.dataset.profileid !== String(raw.profileid)
                && form.elements.profileid.value === String(raw.profileid);
            if (profileChanged) {
                const filterbar = form.querySelector('.multipicker-filterbar');
                await Templates.replaceNodeContents(filterbar, next.querySelector('.multipicker-filterbar').innerHTML, '');
                const options = form.querySelector('[data-multipicker-options] .multipicker-filterbar');
                await Templates.replaceNodeContents(options,
                    next.querySelector('[data-multipicker-options] .multipicker-filterbar').innerHTML, '');
                form.dataset.profileid = String(raw.profileid);
            } else {
                next.querySelectorAll('datalist').forEach(list => {
                    const current = form.querySelector('#' + list.id);
                    if (current) {
                        current.replaceChildren(...list.children);
                    }
                });
            }
            const panel = form.closest('[data-multipicker-panel]');
            panel.querySelectorAll(':scope > .alert').forEach(alert => alert.remove());
            response.querySelectorAll('[data-multipicker-panel] > .alert').forEach(alert => panel.prepend(alert));
            results.dataset.refresh = String(Number(results.dataset.refresh || 0) + 1);
        } catch (error) {
            Notification.exception(error);
        } finally {
            loading = false;
            if (form.isConnected) {
                form.removeAttribute('aria-busy');
                form.querySelector('[data-multipicker-results]').classList.remove('multipicker-loading');
                actions.forEach(button => {
                    button.disabled = button.dataset.multipickerDisabled === 'true';
                });
                form.querySelector('[data-multipicker-loading]').textContent = '';
                update();
                if (retry) {
                    timer = setTimeout(() => apply(toggleitem), 250);
                }
            }
        }
    };
    // Remember disclosures without changing server-side authority or selection.
    for (const detail of form.querySelectorAll('details[data-multipicker-options]')) {
        const key = 'multipicker-options';
        try {
            detail.open = sessionStorage.getItem(key) === 'open';
        } catch (error) {
            // Storage can be disabled in embedded documents; native disclosures still work.
        }
        detail.addEventListener('toggle', () => {
            try {
                sessionStorage.setItem(key, detail.open ? 'open' : 'closed');
            } catch (error) {
                // Presentation state is optional.
            }
        });
    }
    form.addEventListener('input', event => {
        changes++;
        if (event.target.matches('input[data-multipicker-filter]')) {
            clearTimeout(timer);
            timer = setTimeout(() => apply(), 500);
        }
    });
    form.addEventListener('change', event => {
        changes++;
        update();
        if (event.target.matches('[data-multipicker-choice]')) {
            apply(event.target.value);
        }
        if (event.target.matches('select[data-multipicker-filter]')) {
            clearTimeout(timer);
            apply();
        }
    });
    form.addEventListener('click', event => {
        const card = event.target.closest('.multipicker-card');
        if (!card || event.target.closest('button, a, input, label, summary')) {
            return;
        }
        const checkbox = card.querySelector('input[type=checkbox]');
        if (!checkbox.disabled && !loading) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change', {bubbles: true}));
        }
    });
    form.addEventListener('submit', event => {
        clearTimeout(timer);
        if (event.submitter?.name === 'search') {
            event.preventDefault();
            apply();
            return;
        }
        if (submitting || loading) {
            event.preventDefault();
            return;
        }
        submitting = true;
        form.setAttribute('aria-busy', 'true');
        getString('filterloading', 'local_multipicker').then(text => {
            form.querySelector('[data-multipicker-loading]').textContent = text;
            return text;
        }).catch(() => {});
    });
    form.querySelectorAll('[data-multipicker-layout]').forEach(button => {
        button.addEventListener('click', () => {
            changes++;
            form.dataset.layout = button.dataset.multipickerLayout;
            form.elements.layout.value = button.dataset.multipickerLayout;
            form.querySelectorAll('[data-multipicker-layout]').forEach(other => {
                other.setAttribute('aria-pressed', String(other === button));
            });
        });
    });
    update();
};
