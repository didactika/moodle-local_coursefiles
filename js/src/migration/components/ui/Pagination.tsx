/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar under a list: how many rows a page holds, the rows that are shown out of all, and the arrows to the previous and next page.
 * It is always at the bottom of the list, where the eye ends.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Text} from '../../types';
import Icon from './Icon';

interface PaginationProps {
    /** The page shown, from 0. */
    page: number;
    perpage: number;
    total: number;
    onPage: (page: number) => void;
    text: Text;
    /** The sizes of a page to choose from, when the person can choose. */
    sizes?: number[];
    onPerPage?: (size: number) => void;
}

const Pagination = ({page, perpage, total, onPage, text, sizes, onPerPage}: PaginationProps) => {
    const pages = Math.max(1, Math.ceil(total / Math.max(1, perpage)));
    const choose = sizes !== undefined && onPerPage !== undefined && total > 0 ? {sizes, onPerPage} : null;
    if (pages < 2 && choose === null) {
        return null;
    }
    const from = total > 0 ? page * perpage + 1 : 0;
    const to = Math.min((page + 1) * perpage, total);
    const arrow = (target: number, enabled: boolean, icon: string, title: string) => <button
        type="button"
        className="btn btn-link btn-icon"
        title={title}
        aria-label={title}
        disabled={!enabled}
        onClick={() => onPage(target)}
    >
        <Icon name={icon} />
    </button>;

    return <nav aria-label={text('pagination_label')} className="fbm-listbar fbm-pagination">
        <div className="fbm-listbar-size">
            {choose !== null && <>
                <span className="fbm-listbar-label">{text('per_page')}</span>
                <select
                    className="form-select form-select-sm rounded-pill"
                    aria-label={text('per_page')}
                    value={perpage}
                    onChange={(event) => choose.onPerPage(Number(event.target.value))}
                >
                    {choose.sizes.map((size) => <option key={size} value={size}>{size}</option>)}
                </select>
            </>}
        </div>
        <div className="fbm-listbar-pages">
            <span className="text-muted small" aria-live="polite">{text('pagination_range', {from, to, total})}</span>
            <div className="fbm-actions">
                {arrow(page - 1, page > 0, 'chevron-left', text('pagination_previous'))}
                {arrow(page + 1, page < pages - 1, 'chevron-right', text('pagination_next'))}
            </div>
        </div>
    </nav>;
};

export default Pagination;
