/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What can be done with what was chosen. It floats at the bottom of the screen while there is a choice, so the page does not move
 * and the actions are always at hand.
 *
 * @module     local_filebank/migration/components/ui/FloatingBar
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import Icon from './Icon';

interface FloatingBarProps {
    /** What it says about the choice, such as how many things are chosen. */
    summary: ReactNode;
    children: ReactNode;
    onClear: () => void;
    clearLabel: string;
}

const FloatingBar = ({summary, children, onClear, clearLabel}: FloatingBarProps) => <div className="fbm-floating" role="status" data-region="bulk-actions">
    <span className="fbm-floating-summary">{summary}</span>
    <div className="fbm-floating-actions">{children}</div>
    <button type="button" className="fbm-floating-clear" onClick={onClear} title={clearLabel}>
        <Icon name="xmark" /><span className="fbm-sr">{clearLabel}</span>
    </button>
</div>;

export default FloatingBar;
