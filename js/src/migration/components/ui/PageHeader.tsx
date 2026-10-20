/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The head of a page: its title, a line that says what it is for and the actions that belong to the whole page.
 *
 * @module     local_filebank/migration/components/ui/PageHeader
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';

interface PageHeaderProps {
    title: ReactNode;
    subtitle?: ReactNode;
    /** What goes after the title, such as a badge. */
    badges?: ReactNode;
    /** What goes before the title, such as the way back. */
    back?: ReactNode;
    actions?: ReactNode;
}

const PageHeader = ({title, subtitle, badges, back, actions}: PageHeaderProps) => <header className="fbm-header">
    <div className="fbm-header-title">
        {back}
        <div className="fbm-header-line">
            <h2 className="h2">{title}</h2>
            {badges}
        </div>
        {subtitle && <p className="text-muted fbm-header-subtitle">{subtitle}</p>}
    </div>
    {actions && <div className="fbm-header-actions">{actions}</div>}
</header>;

export default PageHeader;
