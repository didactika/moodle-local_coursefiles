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

export interface Route {
    /** The migration that is open, or 0 for the list. */
    planid: number;
    open: (planid: number) => void;
}

/**
 * Follow the address.
 *
 * @param initial The migration the page was opened on, or 0.
 * @param pageurl The address of the page, without its query.
 * @returns The migration that is open and how to open another one or none.
 */
export const useRoute = (initial: number, pageurl: string): Route => {
    const [planid, setPlanid] = useState(initial);

    useEffect(() => {
        const follow = () => setPlanid(Number(new URLSearchParams(window.location.search).get('id')) || 0);
        window.addEventListener('popstate', follow);
        return () => window.removeEventListener('popstate', follow);
    }, []);

    const open = useCallback((target: number) => {
        const address = target > 0 ? `${pageurl}${pageurl.includes('?') ? '&' : '?'}id=${target}` : pageurl;
        window.history.pushState({}, '', address);
        setPlanid(target);
        window.scrollTo({top: 0});
    }, [pageurl]);

    return {planid, open};
};
