/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the AMD module that mounts the migration screens gives them from Moodle: the web service client and the modules that the
 * screens load by name.
 *
 * @module     local_filebank/shims/host
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** A request of the web service client of Moodle. */
export interface AjaxRequest {
    methodname: string;
    args: Record<string, unknown>;
}

/** The modules of Moodle that the screens use. */
export interface Host {
    ajax: {call: (requests: AjaxRequest[]) => Array<PromiseLike<unknown>>};
    modules: Record<string, unknown>;
}

let current: Host | null = null;

/**
 * Keep the modules of Moodle.
 *
 * @param host What the AMD module loaded.
 */
export const setHost = (host: Host): void => {
    current = host;
};

/**
 * The modules of Moodle.
 *
 * @returns What was given when the screens were mounted.
 */
export const getHost = (): Host => {
    if (current === null) {
        throw new Error('The migration screens were not mounted.');
    }
    return current;
};
