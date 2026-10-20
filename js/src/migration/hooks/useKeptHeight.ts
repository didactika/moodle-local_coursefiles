/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Keep the height of a list when it goes to another page, so the bar under it stays where it was and a last page with fewer rows
 * does not make the page jump; a new search or filter lets the list take its own height again.
 *
 * @module     local_filebank/migration/hooks/useKeptHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useRef, useState, type CSSProperties} from 'react';

/**
 * The height a list keeps, the element that measures it and how to keep or release it.
 *
 * @returns The ref of the element, its style, and keep and release. The type of the ref is left to TypeScript, because React 18 and
 *     React 19 type a ref made with null differently.
 */
export const useKeptHeight = () => {
    const ref = useRef<HTMLDivElement>(null);
    const [height, setHeight] = useState(0);
    const style: CSSProperties | undefined = height > 0 ? {minHeight: `${height}px`} : undefined;
    return {
        ref,
        style,
        /** Remember the height the list has now, before it changes page. */
        keep: () => setHeight((current) => Math.max(current, ref.current?.offsetHeight ?? 0)),
        /** Let the list take its own height again. */
        release: () => setHeight(0),
    };
};
