/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Mounts the migration screens in a page of Moodle 4.0 to 5.2, where Preact draws them through the interface of React.
 *
 * @module     local_filebank/entry
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {createRoot} from 'react-dom/client';
import Migration from './Migration';
import type {MigrationProps} from './migration/types';
import {setHost, type Host} from '../shims/host';

/**
 * Draw the migrations in an element.
 *
 * @param element Where to draw them.
 * @param props What the page gives them: the migration to open, the sizes of a page and the text.
 * @param host The modules of Moodle that they use.
 */
export const mount = (element: HTMLElement, props: MigrationProps, host: Host): void => {
    setHost(host);
    createRoot(element).render(<Migration {...props} />);
};
