// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Compatibility entry point for native Moodle AMD calls; all behavior is TypeScript ESM.
 *
 * @module local_coursefiles/administration
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Controller from 'core/esm!@moodle/lms/local_coursefiles/local/admin/controller';

export const init = Controller.init;
