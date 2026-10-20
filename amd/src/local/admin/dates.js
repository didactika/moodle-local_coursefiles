// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * The date fields of the filters. Each date is asked for on its own with a calendar, so a list can be filtered from a day, up to a
 * day, or between two; the first date cannot be after the second, and each one can be taken away with its own button.
 *
 * @module local_filebank/local/admin/dates
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import moment from 'local_filebank/datelib/moment';
import 'local_filebank/datelib/daterangepicker';

const FORMAT = 'YYYY-MM-DD';

/**
 * The parts of one date field.
 *
 * @param {HTMLElement} field The field.
 * @returns {{display: HTMLInputElement, value: HTMLInputElement, clear: HTMLButtonElement}} Its inputs and its button.
 */
const partsOf = field => ({
    display: field.querySelector('[data-filebank-date-display]'),
    value: field.querySelector('[data-filebank-date-value]'),
    clear: field.querySelector('[data-filebank-date-clear]'),
});

/**
 * Give a date to a field, or take it away, and tell its form.
 *
 * @param {HTMLElement} field The field.
 * @param {string} value The date as YYYY-MM-DD, or an empty string.
 */
const setDate = (field, value) => {
    const {display, value: hidden, clear} = partsOf(field);
    hidden.value = value;
    display.value = value === '' ? '' : moment(value, FORMAT).format('LL');
    clear.hidden = value === '';
    hidden.dispatchEvent(new Event('change', {bubbles: true}));
};

/**
 * Turn the date fields of the page into calendars.
 */
export const init = () => {
    const fields = [...document.querySelectorAll('[data-filebank-date]')];
    if (!fields.length || !$.fn.daterangepicker) {
        return;
    }
    const form = fields[0].closest('form');
    moment.locale((form?.dataset.lang || 'en').replace('_', '-'));
    const data = moment.localeData();
    fields.forEach((field, index) => {
        const {display, value, clear} = partsOf(field);
        const other = fields[index === 0 ? 1 : 0];
        $(display).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            showDropdowns: true,
            opens: 'left',
            locale: {format: FORMAT, daysOfWeek: data.weekdaysMin(), monthNames: data.months(), firstDay: data.firstDayOfWeek()},
        });
        const picker = $(display).data('daterangepicker');
        $(display).on('show.daterangepicker', () => {
            // The first date cannot be after the second, nor the second before the first.
            const limit = other ? partsOf(other).value.value : '';
            picker.minDate = index === 1 && limit ? moment(limit, FORMAT) : false;
            picker.maxDate = index === 0 && limit ? moment(limit, FORMAT) : false;
            if (value.value) {
                picker.setStartDate(moment(value.value, FORMAT));
                picker.setEndDate(moment(value.value, FORMAT));
            }
            picker.updateView();
        });
        $(display).on('apply.daterangepicker', (event, chosen) => setDate(field, chosen.startDate.format(FORMAT)));
        clear.addEventListener('click', () => setDate(field, ''));
    });
};
