/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What went wrong in a call, as a sentence the person can read.
 *
 * @module     local_filebank/migration/lib/errors
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** What Moodle sends back when a web service fails. */
interface MoodleError {
    message?: string;
    errorcode?: string;
    error?: string;
}

/**
 * The text of an error that a call raised.
 *
 * @param error What was thrown: an exception of Moodle, an Error or anything else.
 * @returns The message, or an empty string when there is nothing to say.
 */
export const describeError = (error: unknown): string => {
    if (typeof error === 'string') {
        return error;
    }
    if (error !== null && typeof error === 'object') {
        const found = error as MoodleError;
        return found.message ?? found.error ?? found.errorcode ?? '';
    }
    return '';
};

/**
 * Whether the migration changed under the person since it was read.
 *
 * @param error What was thrown.
 * @returns True when the server refused a change because the migration is not what the person was looking at.
 */
export const isStale = (error: unknown): boolean => {
    if (error !== null && typeof error === 'object') {
        return (error as MoodleError).errorcode === 'planstale';
    }
    return false;
};
