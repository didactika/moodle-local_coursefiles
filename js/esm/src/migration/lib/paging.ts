/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which pages a bar of pages shows around the one that is open.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export interface PageWindow {
    pages: number;
    shown: number[];
    first: boolean;
    last: boolean;
    startDots: boolean;
    endDots: boolean;
}

/**
 * The pages to show.
 *
 * @param page Zero-based page that is open.
 * @param perpage Size of the page.
 * @param total How many things there are.
 * @param around How many pages to show on each side of the open one.
 * @returns The number of pages and the ones to write.
 */
export const pageWindow = (page: number, perpage: number, total: number, around = 2): PageWindow => {
    const pages = Math.max(1, Math.ceil(total / Math.max(1, perpage)));
    const start = Math.max(0, Math.min(page - around, pages - 1 - around * 2));
    const end = Math.min(pages - 1, Math.max(page + around, around * 2));
    const shown: number[] = [];
    for (let index = Math.max(0, start); index <= end; index++) {
        shown.push(index);
    }
    return {
        pages,
        shown,
        first: shown[0] > 0,
        last: shown[shown.length - 1] < pages - 1,
        startDots: shown[0] > 1,
        endDots: shown[shown.length - 1] < pages - 2,
    };
};
