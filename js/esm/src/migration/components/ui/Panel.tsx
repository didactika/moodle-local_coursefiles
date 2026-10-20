/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A section of a page: a card with a title, an icon, how many things it holds and, when it has them, its own actions.
 *
 * @module     local_filebank/migration/components/ui/Panel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';

interface PanelProps {
    title: string;
    icon: string;
    count?: number;
    actions?: ReactNode;
    /** The body has no padding, because it holds a table or a list. */
    flush?: boolean;
    region?: string;
    children: ReactNode;
}

const Panel = ({title, icon, count, actions, flush = false, region, children}: PanelProps) => <section className="fbm-panel" data-region={region}>
    <header className="fbm-panel-head">
        <h3 className="fbm-panel-title">
            <span className="fbm-panel-icon"><Icon name={icon} gap /></span>
            {title}
            {count !== undefined && <span className="fbm-count">{count}</span>}
        </h3>
        {actions && <div className="fbm-panel-actions">{actions}</div>}
    </header>
    <div className={flush ? 'fbm-panel-flush' : 'fbm-panel-body'}>{children}</div>
</section>;

export default Panel;
