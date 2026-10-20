/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A row of choices of which one is on, drawn as pills: the groups of files, the size of a page.
 *
 * @module     local_filebank/migration/components/ui/Chips
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export interface Chip {
    value: string;
    label: string;
    /** How many things the choice holds. */
    count?: number;
}

interface ChipsProps {
    label: string;
    value: string;
    chips: Chip[];
    onChange: (value: string) => void;
}

const Chips = ({label, value, chips, onChange}: ChipsProps) => <div className="fbm-chips" role="group" aria-label={label}>
    {chips.map((chip) => <button
        key={chip.value}
        type="button"
        className={`btn btn-sm btn-outline-secondary fbm-chip${chip.value === value ? ' active' : ''}`}
        aria-pressed={chip.value === value}
        onClick={() => onChange(chip.value)}
    >
        {chip.label}
        {chip.count !== undefined && <span className="badge rounded-pill fbm-chip-count">{chip.count}</span>}
    </button>)}
</div>;

export default Chips;
