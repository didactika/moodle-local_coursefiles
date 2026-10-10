// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Compatibility entry point for native Moodle AMD calls; all behavior is TypeScript ESM.
 *
 * @module local_multipicker/browser
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Controller from 'core/esm!@moodle/lms/local_multipicker/local/selection/controller';

export const selection = Controller.selection;

import Bridge from 'core/esm!@moodle/lms/local_multipicker/local/picker/bridge';

export const presentation = Bridge.presentation;
export const complete = Bridge.complete;
