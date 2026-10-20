/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A figure with its label: how many files would be copied, how many wait for a decision. It is a button when the figure leads
 * somewhere, such as the list of the things it counts.
 *
 * @module     local_filebank/migration/components/ui/StatTile
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';

export type Tone = 'neutral' | 'primary' | 'success' | 'warning' | 'danger' | 'info';

interface StatTileProps {
    label: string;
    value: ReactNode;
    tone?: Tone;
    icon?: string;
    /** What goes under the figure. */
    hint?: ReactNode;
    onClick?: () => void;
    active?: boolean;
}

const StatTile = ({label, value, tone = 'neutral', icon, hint, onClick, active = false}: StatTileProps) => {
    const body = <>
        <span className="fbm-tile-top">
            <span className="fbm-tile-label">{label}</span>
            {icon && <span className="fbm-tile-icon"><Icon name={icon} /></span>}
        </span>
        <span className="fbm-tile-value">{value}</span>
        {hint && <span className="fbm-tile-hint">{hint}</span>}
    </>;
    const className = `fbm-tile tone-${tone}${active ? ' is-active' : ''}${onClick ? ' is-link' : ''}`;
    return onClick
        ? <button type="button" className={className} onClick={onClick} aria-pressed={active}>{body}</button>
        : <div className={className}>{body}</div>;
};

export default StatTile;
