/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar that goes from one page of a list to another, without leaving the page.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {ReactNode} from 'react';
import {pageWindow} from '../../lib/paging';
import type {Text} from '../../types';
import Icon from './Icon';

interface PaginationProps {
    page: number;
    perpage: number;
    total: number;
    onPage: (page: number) => void;
    text: Text;
}

const Pagination = ({page, perpage, total, onPage, text}: PaginationProps) => {
    const bar = pageWindow(page, perpage, total);
    if (bar.pages < 2) {
        return null;
    }
    const go = (target: number, title: string, content: ReactNode, key: string) => <li key={key}>
        <button type="button" className="fbm-page" title={title} aria-label={title} onClick={() => onPage(target)}>{content}</button>
    </li>;

    return <nav aria-label={text('pagination_label')} className="fbm-pagination">
        <ul className="fbm-pages">
            {bar.first && go(0, text('pagination_first'), <Icon name="angles-left" />, 'first')}
            {page > 0 && go(page - 1, text('pagination_previous'), <Icon name="angle-left" />, 'previous')}
            {bar.startDots && <li key="start"><span className="fbm-page is-gap">...</span></li>}
            {bar.shown.map((index) => <li key={index}>
                <button
                    type="button"
                    className={`fbm-page${index === page ? ' is-current' : ''}`}
                    onClick={() => onPage(index)}
                    aria-current={index === page ? 'page' : undefined}
                >
                    {index + 1}
                </button>
            </li>)}
            {bar.endDots && <li key="end"><span className="fbm-page is-gap">...</span></li>}
            {bar.last && go(bar.pages - 1, text('pagination_last'), bar.pages, 'last')}
            {page < bar.pages - 1 && go(page + 1, text('pagination_next'), <Icon name="angle-right" />, 'next')}
        </ul>
        <div className="fbm-muted fbm-pagination-info">{text('pagination_info', {current: page + 1, total: bar.pages, count: total})}</div>
    </nav>;
};

export default Pagination;
