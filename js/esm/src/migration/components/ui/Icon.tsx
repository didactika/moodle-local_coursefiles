/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * An icon of Font Awesome, which Moodle already loads, so it takes the colour and the size of the text around it.
 *
 * @module     local_filebank/migration/components/ui/Icon
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {iconClass} from '../../lib/icons';

interface IconProps {
    /** Name of the icon in Font Awesome, without the fa- prefix. */
    name: string;
    /** Space to leave after the icon, when it comes before a text. */
    gap?: boolean;
    /** Spin it, for something that is being waited for. */
    spin?: boolean;
}

const Icon = ({name, gap = false, spin = false}: IconProps) => <i
    className={`${iconClass(name)}${gap ? ' fbm-gap' : ''}${spin ? ' fa-spin' : ''}`}
    aria-hidden="true"
/>;

export default Icon;
