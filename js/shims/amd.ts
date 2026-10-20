/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Loads a module of Moodle by name. The modules that the screens need were already loaded by the AMD module that mounts them, so
 * this only hands them over.
 *
 * @module     local_filebank/shims/amd
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {getHost} from './host';

/**
 * Get a module of Moodle.
 *
 * @param name Name of the module, such as core/toast.
 * @returns The module.
 */
export const requireAsync = <T>(name: string): Promise<T> => {
    const found = getHost().modules[name];
    return found === undefined ? Promise.reject(new Error('Unknown module ' + name)) : Promise.resolve(found as T);
};
