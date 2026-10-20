/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The classes of an icon of Font Awesome, which Moodle already loads.
 *
 * Moodle 5 loads Font Awesome 6; the build for the older lines replaces this file with one that names the icons of Font Awesome 4.
 *
 * @module     local_filebank/migration/lib/icons
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The classes that draw an icon.
 *
 * @param name Name of the icon in Font Awesome 6, without the fa- prefix.
 * @returns The classes.
 */
export const iconClass = (name: string): string => `fa-solid fa-${name}`;
