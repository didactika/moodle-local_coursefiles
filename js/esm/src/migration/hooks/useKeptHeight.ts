/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Keep the height of a list when it goes to another page, so a last page with fewer rows does not make the page jump; a new
 * search or filter lets it take its own height again.
 *
 * @module     local_filebank/migration/hooks/useKeptHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useRef, useState, type CSSProperties, type RefObject} from 'react';

export interface KeptHeight {
    ref: RefObject<HTMLDivElement>;
    style: CSSProperties | undefined;
    /** Remember the height the list has now, before it changes page. */
    keep: () => void;
    /** Let the list take its own height again. */
    release: () => void;
}

export const useKeptHeight = (): KeptHeight => {
    const ref = useRef<HTMLDivElement>(null);
    const [height, setHeight] = useState(0);
    return {
        ref,
        style: height > 0 ? {minHeight: `${height}px`} : undefined,
        keep: () => setHeight((current) => Math.max(current, ref.current?.offsetHeight ?? 0)),
        release: () => setHeight(0),
    };
};
