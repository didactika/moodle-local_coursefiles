// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Native picker entry points; selection and core integration are separate modules.
 *
 * @module local_multipicker/browser
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export {selection} from 'local_multipicker/local/selection/controller';
export {presentation, complete} from 'local_multipicker/local/picker/bridge';
