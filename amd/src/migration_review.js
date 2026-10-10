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
 * Serialized automatic selections and bounded review navigation through core Fragment.
 * @module local_multipicker/migration_review
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Fragment from 'core/fragment';
import Templates from 'core/templates';
import Notification from 'core/notification';
import {getStrings} from 'core/str';

/**
 * Enhance an existing review while preserving its native forms and authorization.
 */
export const init = async() => {
    const root = document.querySelector('[data-region=multipicker-migration]');
    if (!root || root.dataset.ready || !root.querySelector('[data-multipicker-migration-selection]')) {
        return;
    }
    const [saving, saved] = await getStrings([
        {key: 'autosaving', component: 'local_multipicker'}, {key: 'autosaved', component: 'local_multipicker'},
    ]);
    let queue = Promise.resolve();
    let pending = 0;
    let failed = false;
    let canContinue = root.querySelector('[data-multipicker-continue]').getAttribute('aria-disabled') === 'false';
    let searchTimer;
    let viewSequence = 0;
    let resolving = false;
    const continuation = () => {
        const button = root.querySelector('[data-multipicker-continue]');
        const disabled = !canContinue || pending > 0 || failed;
        button.classList.toggle('disabled', disabled);
        button.setAttribute('aria-disabled', String(disabled));
        button.tabIndex = disabled ? -1 : 0;
        root.querySelector('[data-multipicker-blockedhelp]').hidden = canContinue;
        root.dataset.pending = String(pending);
        root.querySelectorAll('input[type=checkbox], [data-multipicker-resolve], [data-multipicker-file-resolution]')
            .forEach(control => {
                control.disabled = resolving;
            });
    };
    const formState = () => {
        const form = root.querySelector('[data-multipicker-migration-selection]');
        const data = new FormData(form);
        return {
            q: data.get('q'), page: data.get('page'), issuepage: data.get('issuepage'),
            decision: data.get('decision'), perpage: data.get('perpage'),
            visible: data.getAll('visible[]'), included: data.getAll('included[]'),
        };
    };
    const checks = () => [...root.querySelectorAll('input[name="included[]"]')];
    const setPartial = (control, included, total) => {
        control.checked = total > 0 && included === total;
        control.indeterminate = included > 0 && included < total;
    };
    const patch = state => {
        root.dataset.digest = state.digest;
        root.dataset.total = state.total;
        root.dataset.included = state.included;
        root.querySelectorAll('input[name=digest]').forEach(input => {
            input.value = state.digest;
        });
        root.querySelector('[data-multipicker-planstate]').textContent = state.planstate;
        root.querySelector('[data-multipicker-includedlabel]').textContent = state.includedlabel;
        root.querySelector('[data-multipicker-empty]').hidden = !state.emptycancelled;
        canContinue = state.cancontinue;
        if (pending === 1) {
            state.checks.forEach(value => {
                const check = checks().find(input => input.value === String(value.id));
                if (check) {
                    check.checked = value.included;
                    check.closest('[data-multipicker-operation]').querySelector('[data-multipicker-excluded]').hidden
                        = value.included;
                }
            });
            state.groups.forEach(group => {
                const selector = group.cmid ? '[data-multipicker-folder="' + group.cmid + '"]'
                    : '[data-multipicker-course="' + group.courseid + '"]';
                root.querySelectorAll(selector).forEach(control => {
                    control.dataset.included = group.included;
                    control.dataset.total = group.total;
                    setPartial(control, group.included, group.total);
                });
            });
            const all = root.querySelector('[data-multipicker-selectall]');
            if (all) {
                setPartial(all, state.included, state.total);
            }
        }
    };
    const request = state => {
        const sequence = state.action === 'view' ? ++viewSequence : 0;
        if (state.action === 'resolve') {
            resolving = true;
        }
        pending++;
        root.setAttribute('aria-busy', 'true');
        const status = root.querySelector('[data-multipicker-save-status]');
        if (status) {
            status.textContent = saving;
        }
        continuation();
        queue = queue.then(async() => {
            if (failed) {
                return;
            }
            const payload = {...state, planid: root.dataset.planid, digest: root.dataset.digest, sesskey: root.dataset.sesskey};
            const result = await new Promise((resolve, reject) => {
                Fragment.loadFragment('local_multipicker', 'migration', Number(root.dataset.contextid), {
                    state: JSON.stringify(payload),
                }).done((html, js) => resolve({html, js})).fail(reject);
            });
            const document = new DOMParser().parseFromString(result.html, 'text/html');
            const selection = document.querySelector('[data-multipicker-selection-state]');
            if (selection) {
                patch(JSON.parse(selection.dataset.multipickerSelectionState));
            } else {
                if (state.action === 'view' && sequence !== viewSequence) {
                    return;
                }
                const next = document.querySelector('[data-region=multipicker-migration]');
                const open = new Map([...root.querySelectorAll('details[data-multipicker-group],'
                    + 'details[data-multipicker-folder-group]')].map(element => [
                    element.dataset.multipickerGroup || 'folder:' + element.dataset.multipickerFolderGroup, element.open,
                ]));
                const focus = root.contains(window.document.activeElement) ? window.document.activeElement.id : '';
                const position = window.document.activeElement.selectionStart;
                const searchValue = root.querySelector('#multipicker-migration-q').value;
                const scroll = window.scrollY;
                await Templates.replaceNodeContents(root, next.innerHTML, result.js);
                Object.assign(root.dataset, next.dataset);
                root.dataset.ready = 'true';
                canContinue = root.querySelector('[data-multipicker-continue]').getAttribute('aria-disabled') === 'false';
                root.querySelectorAll('details[data-multipicker-group],details[data-multipicker-folder-group]')
                    .forEach(element => {
                        const key = element.dataset.multipickerGroup || 'folder:' + element.dataset.multipickerFolderGroup;
                        if (open.has(key)) {
                            element.open = open.get(key);
                        }
                    });
                root.querySelectorAll('[data-partial]').forEach(control => {
                    control.indeterminate = control.dataset.partial === '1';
                });
                const field = focus && window.document.getElementById(focus);
                if (field) {
                    if (field.type === 'search') {
                        field.value = searchValue;
                    }
                    field.focus({preventScroll: true});
                    if (typeof position === 'number' && field.type === 'search') {
                        field.setSelectionRange(position, position);
                    }
                }
                window.scrollTo(0, scroll);
                const url = new URL(root.querySelector('[data-multipicker-migration-selection]').getAttribute('action'));
                const form = formState();
                ['q', 'page', 'decision', 'issuepage', 'perpage'].forEach(key => url.searchParams.set(key, form[key]));
                window.history.replaceState(null, '', url);
            }
        }).catch(error => {
            failed = true;
            Notification.exception(error);
        }).finally(() => {
            if (state.action === 'resolve') {
                resolving = false;
            }
            pending--;
            continuation();
            if (!pending) {
                root.setAttribute('aria-busy', 'false');
                root.dataset.saved = String(Number(root.dataset.saved || 0) + 1);
                const status = root.querySelector('[data-multipicker-save-status]');
                if (status) {
                    status.textContent = failed ? '' : saved;
                }
            }
        });
    };
    root.querySelectorAll('[data-partial]').forEach(control => {
        control.indeterminate = control.dataset.partial === '1';
    });
    root.addEventListener('change', event => {
        const control = event.target;
        if (control.matches('[data-multipicker-selectall]')) {
            checks().forEach(check => {
                check.checked = control.checked;
            });
            root.querySelectorAll('[data-multipicker-course],[data-multipicker-folder]').forEach(check => {
                check.checked = control.checked;
                check.indeterminate = false;
            });
            request({...formState(), action: control.checked ? 'all' : 'none'});
        } else if (control.matches('[data-multipicker-course],[data-multipicker-folder]')) {
            const folder = control.hasAttribute('data-multipicker-folder');
            const courseid = folder ? control.dataset.course : control.dataset.multipickerCourse;
            const cmid = folder ? control.dataset.multipickerFolder : '0';
            checks().filter(check => check.dataset.sourceCourse === courseid
                && (!folder || check.dataset.sourceFolder === cmid)).forEach(check => {
                check.checked = control.checked;
            });
            request({...formState(), action: 'group', courseid, cmid, groupincluded: control.checked ? 1 : 0});
        } else if (control.matches('input[name="included[]"]')) {
            control.closest('[data-multipicker-operation]').querySelector('[data-multipicker-excluded]').hidden = control.checked;
            request({...formState(), action: 'page'});
        } else if (control.matches('[data-multipicker-resolve]') && control.value) {
            request({...formState(), action: 'resolve', kind: 'issue',
                id: control.closest('form').querySelector('[name=id]').value, choice: control.value});
        } else if (control.matches('[data-multipicker-file-resolution]') && control.value) {
            request({...formState(), action: 'resolve', kind: 'file', id: control.dataset.multipickerFileResolution,
                choice: control.value});
        } else if (control.matches('[data-multipicker-pagesize]')) {
            const state = formState();
            state[control.dataset.pageparameter] = 0;
            request({...state, action: 'view', perpage: control.value});
        }
    });
    root.addEventListener('submit', event => {
        if (event.target.matches('[data-multipicker-review-search]')) {
            event.preventDefault();
            clearTimeout(searchTimer);
            request({...formState(), action: 'view', page: 0, q: new FormData(event.target).get('q')});
        } else if (event.target.matches('[data-multipicker-migration-selection]')) {
            event.preventDefault();
            request({...formState(), action: 'page'});
        }
    });
    root.addEventListener('input', event => {
        if (event.target.matches('#multipicker-migration-q')) {
            viewSequence++;
            clearTimeout(searchTimer);
            const q = event.target.value;
            searchTimer = setTimeout(() => request({...formState(), action: 'view', page: 0, q}), 350);
        }
    });
    root.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link) {
            return;
        }
        if (link.hasAttribute('data-multipicker-continue') && (pending || !canContinue || failed)) {
            event.preventDefault();
            return;
        }
        const url = new URL(link.href);
        if (url.pathname === window.location.pathname && url.searchParams.get('planid') === root.dataset.planid
            && url.searchParams.get('step') !== 'execute') {
            event.preventDefault();
            request({...formState(), action: 'view', page: url.searchParams.get('page') || 0,
                q: url.searchParams.get('q') || '', decision: url.searchParams.get('decision') || '',
                issuepage: url.searchParams.get('issuepage') || 0});
        }
    });
    root.dataset.ready = 'true';
};
