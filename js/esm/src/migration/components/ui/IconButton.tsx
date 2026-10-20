/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A button that is only an icon, tinted with the colour of what it does. The label is read by assistive technology and is the title
 * of the button, so a row of them does not need a word on each.
 *
 * @module     local_filebank/migration/components/ui/IconButton
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from './Icon';
import type {Tone} from './StatTile';

interface IconButtonProps {
    icon: string;
    label: string;
    onClick: () => void;
    /** The colour of what it does: danger for what removes, primary for what opens. */
    tone?: Tone;
    disabled?: boolean;
}

const IconButton = ({icon, label, onClick, tone = 'neutral', disabled = false}: IconButtonProps) => <button
    type="button"
    className={`btn btn-outline-secondary fbm-iconpill${tone === 'neutral' ? '' : ` tone-${tone}`}`}
    title={label}
    disabled={disabled}
    onClick={onClick}
>
    <Icon name={icon} /><span className="filebank-visually-hidden">{label}</span>
</button>;

export default IconButton;
