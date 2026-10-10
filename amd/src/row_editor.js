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

import {getStrings} from 'core/str';
import Templates from 'core/templates';
import Notification from 'core/notification';

/**
 * Enhance a native textarea with bounded, editable form rows.
 *
 * @param {string} id Native field ID.
 * @param {Array} columns Server-issued column contracts.
 * @param {number} maximum Row limit enforced again by PHP.
 */
export const init = async(id, columns, maximum) => {
    const field = document.getElementById(id);
    if (!field || field.dataset.multipickerRows) {
        return;
    }
    try {
        const values = JSON.parse(field.value || '[]');
        if (!Array.isArray(values) || values.length > maximum) {
            return;
        }
        const [add, remove] = await getStrings([
            {key: 'addrow', component: 'local_multipicker'}, {key: 'delete', component: 'core'},
        ]);
        const {html, js} = await Templates.renderForPromise('local_multipicker/local/form/rows', {columns, add});
        const wrapper = document.createElement('div');
        field.after(wrapper);
        await Templates.replaceNodeContents(wrapper, html, js);
        const body = wrapper.querySelector('tbody');
        const button = wrapper.querySelector('[data-multipicker-addrow]');
        const synchronize = () => {
            const rows = [...body.rows].map(row => Object.fromEntries(columns.map(column => {
                const input = row.querySelector('[data-field="' + column.key + '"]');
                return [column.key, column.type === 'checkbox' ? input.checked : input.value];
            })));
            field.value = JSON.stringify(rows);
            button.disabled = rows.length >= maximum;
        };
        const append = async(value = {}) => {
            const row = document.createElement('tr');
            for (const column of columns) {
                const cell = row.insertCell();
                const input = document.createElement(column.options ? 'select' : 'input');
                input.dataset.field = column.key;
                input.setAttribute('aria-label', column.label);
                if (column.options) {
                    column.options.forEach(option => input.add(new Option(option.label, option.value)));
                } else {
                    input.type = column.type;
                    if (column.type === 'checkbox') {
                        input.checked = Boolean(value[column.key]);
                    } else {
                        input.maxLength = column.maxlength || 2048;
                        input.required = true;
                        if (['key', 'capture'].includes(column.key)) {
                            input.pattern = '[A-Za-z][A-Za-z0-9_]{0,63}';
                        }
                    }
                }
                input.className = column.type === 'checkbox' ? 'multipicker-row-check'
                    : (column.options ? 'custom-select custom-select-sm' : 'form-control form-control-sm');
                if (column.type !== 'checkbox') {
                    input.value = value[column.key] || column.options?.[0]?.value || '';
                }
                input.addEventListener('input', synchronize);
                cell.append(input);
            }
            const cell = row.insertCell();
            const {html, js} = await Templates.renderForPromise('local_multipicker/local/form/remove_row', {remove});
            await Templates.replaceNodeContents(cell, html, js);
            cell.querySelector('button').addEventListener('click', () => {
                row.remove();
                synchronize();
                button.focus();
            });
            body.append(row);
            return row;
        };
        for (const value of values) {
            await append(value);
        }
        field.hidden = true;
        field.dataset.multipickerRows = 'true';
        button.addEventListener('click', async() => {
            if (body.rows.length >= maximum) {
                return;
            }
            button.disabled = true;
            try {
                const row = await append();
                row.querySelector('input, select').focus();
            } catch (error) {
                Notification.exception(error);
            } finally {
                synchronize();
            }
        });
        synchronize();
    } catch (error) {
        Notification.exception(error);
    }
};

/**
 * Advanced rules replace the guided sections, including their native legends.
 */
export const profile = () => {
    const mode = document.getElementById('id_editmode');
    if (!mode) {
        return;
    }
    const update = () => {
        for (const name of ['identitysettings', 'capturefilters', 'sourcesettings', 'rules']) {
            const section = document.querySelector('fieldset[id*="' + name + '"]');
            if (section) {
                const hidden = name === 'rules' ? mode.value !== 'advanced' : mode.value === 'advanced';
                section.hidden = hidden;
                section.querySelectorAll('input, select, textarea').forEach(input => {
                    if (!input.name.endsWith('json')) {
                        input.disabled = hidden;
                    }
                });
            }
        }
    };
    mode.addEventListener('change', update);
    update();
};
