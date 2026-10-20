/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The filters of a list: a button with the filter icon and the number of filters that are on floating over its corner, and a
 * drop-down with a title, a button that resets them and the choices. A choice applies as soon as it is made, and the filters that
 * are on are also shown outside as chips that take one away. It is the same drop-down that the pages drawn by the server use.
 *
 * @module     local_filebank/migration/components/ui/FilterMenu
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef} from 'react';
import Icon from './Icon';

export interface FilterOption {
    value: string;
    label: string;
    /** How many things the choice holds. */
    count?: number;
}

export interface FilterGroup {
    id: string;
    label: string;
    /** The choice that is applied; an empty one means that the filter is not applied. */
    value: string;
    options: FilterOption[];
    onChange: (value: string) => void;
}

interface FilterMenuProps {
    label: string;
    resetLabel: string;
    groups: FilterGroup[];
    /** The list has a search before the filters, so the button goes to the end of the row. */
    afterSearch?: boolean;
}

const FilterMenu = ({label, resetLabel, groups, afterSearch = true}: FilterMenuProps) => {
    const box = useRef<HTMLDetailsElement>(null);
    const applied = groups.filter((group) => group.value !== '');

    // The drop-down closes with a click outside it or with Escape, as the drop-downs of Bootstrap do.
    useEffect(() => {
        const close = (event: Event) => {
            const element = box.current;
            if (element?.open && (!(event.target instanceof Node) || !element.contains(event.target))) {
                element.open = false;
            }
        };
        const escape = (event: KeyboardEvent) => {
            if (event.key === 'Escape' && box.current) {
                box.current.open = false;
            }
        };
        document.addEventListener('click', close);
        document.addEventListener('keydown', escape);
        return () => {
            document.removeEventListener('click', close);
            document.removeEventListener('keydown', escape);
        };
    }, []);

    return <>
        <details
            ref={box}
            className={`fbm-filterbox${afterSearch ? ' fbm-filter-end' : ''}`}
            data-region="filters"
            data-active={applied.length > 0 ? 'true' : undefined}
        >
            <summary className="btn btn-outline-secondary">
                <Icon name="filter" gap />{label}
                {applied.length > 0 && <span className="badge rounded-pill fbm-count">{applied.length}</span>}
            </summary>
            <div className="fbm-filterpanel">
                <div className="fbm-filter-head">
                    <h3 className="fbm-filter-heading">{label}</h3>
                    <button
                        type="button"
                        className="btn btn-outline-secondary fbm-iconpill"
                        title={resetLabel}
                        disabled={applied.length === 0}
                        onClick={() => applied.forEach((group) => group.onChange(''))}
                    >
                        <Icon name="rotate-right" /><span className="filebank-visually-hidden">{resetLabel}</span>
                    </button>
                </div>
                {groups.map((group) => <fieldset key={group.id} className="fbm-filter-group">
                    <legend className="fbm-filter-title">{group.label}</legend>
                    {group.options.map((option) => <label key={option.value} className="fbm-filter-option">
                        <input
                            type="radio"
                            name={`fbm-filter-${group.id}`}
                            checked={group.value === option.value}
                            onChange={() => group.onChange(option.value)}
                        />
                        <span>{option.label}</span>
                        {option.count !== undefined && <span className="text-muted small">{option.count}</span>}
                    </label>)}
                </fieldset>)}
            </div>
        </details>
        {applied.length > 0 && <div className="fbm-chips" data-region="active-filters">
            {applied.map((group) => <button
                key={group.id}
                type="button"
                className="btn btn-sm btn-outline-secondary rounded-pill fbm-chip"
                onClick={() => group.onChange('')}
            >
                {group.label}: {group.options.find((option) => option.value === group.value)?.label}
                <Icon name="xmark" />
            </button>)}
        </div>}
    </>;
};

export default FilterMenu;
