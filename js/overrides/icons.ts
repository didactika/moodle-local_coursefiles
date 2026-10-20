/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The classes of an icon of Font Awesome for Moodle 4.0 and later. Moodle 4.0 and 4.1 load Font Awesome 4 and the later ones load
 * Font Awesome 6 with the names of version 4 still working, so the names of version 4 are used.
 *
 * @module     local_filebank/overrides/icons
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** The name in Font Awesome 4 of each icon that the screens ask for by its name in Font Awesome 6. */
const NAMES: Record<string, string> = {
    'angle-left': 'angle-left',
    'angle-right': 'angle-right',
    'angles-left': 'angle-double-left',
    'arrow-left': 'arrow-left',
    'arrow-right': 'arrow-right',
    'arrow-right-from-bracket': 'sign-out',
    'arrow-right-to-bracket': 'sign-in',
    ban: 'ban',
    check: 'check',
    'chevron-down': 'chevron-down',
    'chevron-up': 'chevron-up',
    circle: 'circle',
    'circle-check': 'check-circle',
    'circle-exclamation': 'exclamation-circle',
    'circle-info': 'info-circle',
    'circle-question': 'question-circle',
    'circle-xmark': 'times-circle',
    'clipboard-check': 'clipboard',
    clock: 'clock-o',
    copy: 'files-o',
    equals: 'bars',
    'file-circle-exclamation': 'exclamation-circle',
    'file-circle-question': 'question-circle',
    'file-lines': 'file-text-o',
    folder: 'folder',
    'folder-open': 'folder-open',
    'folder-tree': 'sitemap',
    'graduation-cap': 'graduation-cap',
    hand: 'hand-paper-o',
    'hand-pointer': 'hand-pointer-o',
    'hourglass-half': 'hourglass-half',
    'layer-group': 'th-large',
    list: 'list',
    'list-ol': 'list-ol',
    'magnifying-glass': 'search',
    pen: 'pencil',
    'pen-to-square': 'pencil-square-o',
    play: 'play',
    plus: 'plus',
    'right-left': 'exchange',
    rotate: 'refresh',
    sliders: 'sliders',
    spinner: 'spinner',
    square: 'square-o',
    'square-check': 'check-square-o',
    stop: 'stop',
    trash: 'trash',
    'triangle-exclamation': 'exclamation-triangle',
    'wand-magic-sparkles': 'magic',
    xmark: 'times',
};

/**
 * The classes that draw an icon.
 *
 * @param name Name of the icon in Font Awesome 6, without the fa- prefix.
 * @returns The classes.
 */
export const iconClass = (name: string): string => `fa fa-${NAMES[name] ?? name}`;
