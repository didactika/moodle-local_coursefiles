/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a list shows when it has nothing to list: what happened and what the person can do about it.
 *
 * @module     local_filebank/migration/components/ui/EmptyState
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';

interface EmptyStateProps {
    icon: string;
    title: string;
    text: string;
    actions?: ReactNode;
    /** Draw it without a card around it, inside a dialog or a panel. */
    plain?: boolean;
}

const EmptyState = ({icon, title, text, actions, plain = false}: EmptyStateProps) => <div
    className={`fbm-empty${plain ? ' is-plain' : ''}`}
    data-region="empty-state"
>
    <div className="fbm-empty-icon"><Icon name={icon} /></div>
    <h4 className="h4">{title}</h4>
    <p className="text-muted fbm-empty-text">{text}</p>
    {actions && <div className="fbm-empty-actions">{actions}</div>}
</div>;

export default EmptyState;
