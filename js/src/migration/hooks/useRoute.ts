/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which screen is open, kept in the address so that a migration can be linked to and the back button of the browser works.
 *
 * @module     local_filebank/migration/hooks/useRoute
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useCallback, useEffect, useState} from 'react';

/** The screens: the list, the page that starts a migration, one migration and the page that changes its parameters. */
export type Mode = 'list' | 'new' | 'review' | 'edit';

export interface Location {
    mode: Mode;
    /** The migration that is open, or being edited; 0 when there is none. */
    planid: number;
    /** The migration whose parameters a new one starts from; 0 when there is none. */
    from: number;
}

export interface Route extends Location {
    openList: () => void;
    openNew: (from?: number) => void;
    openReview: (planid: number) => void;
    openEdit: (planid: number) => void;
}

/**
 * Read the screen that an address asks for.
 *
 * @param search The query of the address.
 * @returns The screen.
 */
const read = (search: string): Location => {
    const query = new URLSearchParams(search);
    const planid = Number(query.get('id')) || 0;
    if (planid > 0) {
        return {mode: query.get('edit') === '1' ? 'edit' : 'review', planid, from: 0};
    }
    if (query.get('new') === '1') {
        return {mode: 'new', planid: 0, from: Number(query.get('from')) || 0};
    }
    return {mode: 'list', planid: 0, from: 0};
};

/**
 * Follow the address.
 *
 * @param initial What the page was opened on: the migration, or 0 for the list.
 * @param pageurl The address of the page, without its query.
 * @returns The screen that is open and how to open another one.
 */
export const useRoute = (initial: number, pageurl: string): Route => {
    const [location, setLocation] = useState<Location>(() => (initial > 0
        ? {mode: 'review', planid: initial, from: 0}
        : read(window.location.search)));

    useEffect(() => {
        const follow = () => setLocation(read(window.location.search));
        window.addEventListener('popstate', follow);
        return () => window.removeEventListener('popstate', follow);
    }, []);

    const go = useCallback((target: Location, query: string) => {
        const address = query === '' ? pageurl : `${pageurl}${pageurl.includes('?') ? '&' : '?'}${query}`;
        window.history.pushState({}, '', address);
        setLocation(target);
        window.scrollTo({top: 0});
    }, [pageurl]);

    return {
        ...location,
        openList: () => go({mode: 'list', planid: 0, from: 0}, ''),
        openNew: (from = 0) => go({mode: 'new', planid: 0, from}, from > 0 ? `new=1&from=${from}` : 'new=1'),
        openReview: (planid) => go({mode: 'review', planid, from: 0}, `id=${planid}`),
        openEdit: (planid) => go({mode: 'edit', planid, from: 0}, `id=${planid}&edit=1`),
    };
};
