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
 * Accessible selection controls for native administration forms.
 *
 * @module local_multipicker/administration
 * @copyright 2026 MultiPicker contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Keep select-all synchronized with the checkboxes on the rendered page only.
 */
export const init = () => {
    document.querySelectorAll('[data-multipicker-profiles], [data-multipicker-migration-selection]').forEach(form => {
        const all = form.querySelector('[data-multipicker-selectall]');
        const checks = [...form.querySelectorAll('input[name="profiles[]"], input[name="included[]"]')];
        if (!all) {
            return;
        }
        const update = () => {
            const count = checks.filter(check => check.checked).length;
            all.checked = checks.length > 0 && count === checks.length;
            all.indeterminate = count > 0 && count < checks.length;
            const exportButton = form.querySelector('button[value=export]');
            if (exportButton) {
                exportButton.disabled = count === 0;
            }
        };
        all.addEventListener('change', () => {
            checks.forEach(check => {
                check.checked = all.checked;
            });
            update();
        });
        checks.forEach(check => check.addEventListener('change', update));
        if (form.hasAttribute('data-multipicker-migration-selection')) {
            let dirty = false;
            form.addEventListener('change', () => {
                dirty = true;
            });
            const saveAndNavigate = url => {
                for (const [name, value] of Object.entries({
                    navigate: '1', nextpage: url.searchParams.get('page') || '0', nextq: url.searchParams.get('q') || '',
                    nextdecision: url.searchParams.get('decision') || '', nextstep: url.searchParams.get('step') || '',
                    nextissuepage: url.searchParams.get('issuepage') || '0',
                })) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.append(input);
                }
                form.requestSubmit(form.querySelector('button[value=selection]'));
            };
            const section = form.closest('.multipicker-migration');
            // The native submit buttons named action shadow the form's action property.
            const current = new URL(form.getAttribute('action'), document.baseURI);
            section.querySelectorAll('a[href]').forEach(link => {
                link.addEventListener('click', event => {
                    const url = new URL(link.href);
                    if (dirty && url.pathname === current.pathname
                            && url.searchParams.get('planid') === current.searchParams.get('planid')) {
                        event.preventDefault();
                        saveAndNavigate(url);
                    }
                });
            });
            const search = section.querySelector('form[method=get]');
            search?.addEventListener('submit', event => {
                if (dirty) {
                    event.preventDefault();
                    const url = new URL(current);
                    url.searchParams.set('q', new FormData(search).get('q'));
                    url.searchParams.set('issuepage', new FormData(search).get('issuepage'));
                    saveAndNavigate(url);
                }
            });
        }
        update();
    });
};
