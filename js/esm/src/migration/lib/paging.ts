/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The sizes a page of a list can have, as block_course_finder offers them: the ones smaller than the list, and the whole list when
 * it is not too long, so a page never loads more than the person asked for and "All" never loads a long list at once.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** The sizes a page can have. */
export const PAGE_SIZES = [12, 24, 48, 96];

/** The most rows that "All" shows. */
export const MAX_ALL = 500;

/**
 * The sizes to offer for a list.
 *
 * @param total How many rows the list has.
 * @returns The sizes smaller than the list, and the list itself when it is not too long.
 */
export const pageSizeChoices = (total: number): number[] => {
    if (total <= PAGE_SIZES[0]) {
        return [Math.max(1, total)];
    }
    const sizes = PAGE_SIZES.filter((size) => size < total);
    return total <= MAX_ALL ? [...sizes, total] : sizes;
};
