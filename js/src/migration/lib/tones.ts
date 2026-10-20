/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The class that gives a colour to a badge or an alert. An alert is the one of Bootstrap, so the theme styles it as it styles the rest
 * of the site; a badge is tinted by the plugin from the colours of the theme, and its names are the ones of Bootstrap 5 only because
 * Moodle 5 flags the ones of Bootstrap 4 as deprecated (the rules of the plugin give them the same look on Bootstrap 4).
 *
 * @module     local_filebank/migration/lib/tones
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Tone} from '../components/ui/StatTile';

const BADGES: Record<Tone, string> = {
    primary: 'tone-primary',
    success: 'tone-success',
    warning: 'tone-warning',
    danger: 'tone-danger',
    info: 'tone-info',
    neutral: 'tone-neutral',
};

const ALERTS: Record<Tone, string> = {
    primary: 'alert-primary',
    success: 'alert-success',
    warning: 'alert-warning',
    danger: 'alert-danger',
    info: 'alert-info',
    neutral: 'alert-secondary',
};

/**
 * The classes of a badge.
 *
 * @param tone Its colour.
 * @returns The classes, with the shape of a pill.
 */
export const badgeClasses = (tone: Tone): string => `badge rounded-pill ${BADGES[tone]}`;

/**
 * The classes of an alert.
 *
 * @param tone Its colour.
 * @returns The classes.
 */
export const alertClasses = (tone: Tone): string => `alert ${ALERTS[tone]}`;
