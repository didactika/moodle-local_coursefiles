/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A message inside the page: something that went wrong, something that is good to know.
 *
 * @module     local_filebank/migration/components/ui/Alert
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';
import type {Tone} from './StatTile';

const ICONS: Record<string, string> = {
    danger: 'circle-exclamation',
    warning: 'triangle-exclamation',
    info: 'circle-info',
    success: 'circle-check',
    neutral: 'circle-info',
    primary: 'circle-info',
};

interface AlertProps {
    tone?: Tone;
    children: ReactNode;
    actions?: ReactNode;
    /** The assistive technology reads it as soon as it appears. */
    live?: boolean;
}

const Alert = ({tone = 'info', children, actions, live = false}: AlertProps) => <div
    className={`fbm-alert tone-${tone}`}
    role={live ? 'alert' : 'status'}
>
    <span className="fbm-alert-icon"><Icon name={ICONS[tone]} /></span>
    <div className="fbm-alert-body">{children}</div>
    {actions && <div className="fbm-alert-actions">{actions}</div>}
</div>;

export default Alert;
