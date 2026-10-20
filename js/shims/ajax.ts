/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The web service client of Moodle, with the call that the screens make. Moodle 5.3 has it as a module of its own; here it is the
 * one of core/ajax.
 *
 * @module     local_filebank/shims/ajax
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {getHost, type AjaxRequest} from './host';

/**
 * Call one web service.
 *
 * @param request Name of the function and its arguments.
 * @returns What the function answers.
 */
export const fetchOne = <T>(request: AjaxRequest): Promise<T> => new Promise<T>((resolve, reject) => {
    getHost().ajax.call([request])[0].then((answer) => resolve(answer as T), reject);
});
