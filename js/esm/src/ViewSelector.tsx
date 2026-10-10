// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_multipicker/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from 'react';
import {normalizeLayout, type LayoutView} from './layout';

interface ViewSelectorProps {
    initialLayout: string;
    views: LayoutView[];
}

/**
 * Own view button state while delegated native events preserve the material nodes and focus.
 *
 * Labels and icons come from the server's localized Moodle output renderer.
 */
export default function ViewSelector({initialLayout, views}: ViewSelectorProps) {
    const [active, setActive] = useState(normalizeLayout(initialLayout));

    return <>{views.map(view => <button
        key={view.value}
        type="button"
        className={`btn btn-sm btn-outline-secondary${active === view.value ? ' active' : ''}`}
        data-multipicker-layout={view.value}
        data-multipicker-react-view="true"
        aria-pressed={active === view.value}
        title={view.label}
        onClick={() => setActive(view.value)}
    >
        <span dangerouslySetInnerHTML={{__html: view.icon}} />
        <span className="multipicker-visually-hidden">{view.label}</span>
    </button>)}</>;
}
