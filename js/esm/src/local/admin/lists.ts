/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Lists of server pages that change in place: the arrows, the size of a page and the filters fetch the page and replace only the
 * region of the list (its table and its pager) and the parts of the page that tell about it. The table has a fixed height, so
 * nothing moves.
 *
 * @module local_filebank/local/admin/lists
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Refresh the parts of the page outside the list that tell about it (a count, the size of a page kept by a form).
 *
 * @param page The page that was fetched.
 */
const refresh = (page: Document): void => {
    document.querySelectorAll<HTMLElement>('[data-filebank-refresh]').forEach(element => {
        const twin = page.querySelector<HTMLElement>(`[data-filebank-refresh="${element.dataset.filebankRefresh}"]`);
        if (!twin) {
            return;
        }
        if (element instanceof HTMLInputElement && twin instanceof HTMLInputElement) {
            element.value = twin.value;
        } else {
            element.innerHTML = twin.innerHTML;
        }
    });
    document.querySelectorAll<HTMLElement>('[data-filebank-refresh-active]').forEach(element => {
        const twin = page.querySelector(`[data-filebank-refresh-active="${element.dataset.filebankRefreshActive}"]`);
        element.toggleAttribute('data-active', twin !== null && twin.hasAttribute('data-active'));
    });
};

/**
 * Replace the region of a list with the one of another page.
 *
 * @param origin The control that asked for the page, inside the region, or the region itself.
 * @param url The address of the page.
 */
export const swapList = async(origin: Element, url: string): Promise<void> => {
    const region = origin.closest<HTMLElement>('[data-filebank-list]');
    if (!region) {
        window.location.assign(url);
        return;
    }
    // Only the last page asked for is shown: a search typed quickly does not end on the answer to an older one.
    const request = String(Number(region.dataset.request || 0) + 1);
    region.dataset.request = request;
    region.setAttribute('aria-busy', 'true');
    try {
        const response = await fetch(url, {credentials: 'same-origin'});
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = page.querySelector(`[data-filebank-list="${region.dataset.filebankList}"]`);
        if (region.dataset.request !== request) {
            return;
        }
        if (!response.ok || !next) {
            window.location.assign(url);
            return;
        }
        // The pager stays where it is, so the control the person is using is never taken away from under them: only its options,
        // the range and the arrows are refreshed. Everything else of the region is replaced.
        const pager = region.querySelector<HTMLElement>('.filebank-pagination');
        const nextPager = next.querySelector<HTMLElement>('.filebank-pagination');
        if (pager && nextPager) {
            const select = pager.querySelector<HTMLSelectElement>('select');
            const nextSelect = nextPager.querySelector<HTMLSelectElement>('select');
            if (select && nextSelect) {
                // Only when they changed: replacing the options of a select that someone is using makes them stale.
                if (select.innerHTML !== nextSelect.innerHTML) {
                    select.innerHTML = nextSelect.innerHTML;
                }
                select.value = nextSelect.value;
                select.dataset.url = nextSelect.dataset.url || '';
            }
            const pages = pager.querySelector('.fbm-listbar-pages');
            const nextPages = nextPager.querySelector('.fbm-listbar-pages');
            if (pages && nextPages) {
                pages.replaceWith(document.importNode(nextPages, true));
            }
            nextPager.remove();
            Array.from(region.childNodes).filter(node => node !== pager).forEach(node => node.remove());
            Array.from(next.childNodes).forEach(node => region.insertBefore(document.importNode(node, true), pager));
        } else {
            region.replaceChildren(...Array.from(next.childNodes, node => document.importNode(node, true)));
        }
        refresh(page);
        window.history.replaceState(window.history.state, '', url);
        region.dispatchEvent(new CustomEvent('filebank:listchanged', {bubbles: true}));
    } catch (problem) {
        window.location.assign(url);
    } finally {
        if (region.dataset.request === request) {
            region.removeAttribute('aria-busy');
        }
    }
};

/**
 * Make the arrows of the lists of the page change page in place.
 */
export const registerLists = (): void => {
    if (document.body.dataset.filebankLists) {
        return;
    }
    document.body.dataset.filebankLists = 'true';
    document.addEventListener('click', event => {
        const link = event.target instanceof Element
            ? event.target.closest<HTMLAnchorElement>('[data-filebank-list] .filebank-pagination a[href]') : null;
        if (link) {
            event.preventDefault();
            void swapList(link, link.href);
        }
    });
};
