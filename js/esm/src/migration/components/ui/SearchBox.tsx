/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A search field that filters as the person types, with a way to clear it.
 *
 * @module     local_filebank/migration/components/ui/SearchBox
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from './Icon';

interface SearchBoxProps {
    value: string;
    onChange: (value: string) => void;
    placeholder: string;
    clearLabel: string;
    id: string;
}

const SearchBox = ({value, onChange, placeholder, clearLabel, id}: SearchBoxProps) => <div className="fbm-search">
    <span className="fbm-search-icon"><Icon name="magnifying-glass" /></span>
    <input
        type="search"
        id={id}
        value={value}
        className="fbm-input fbm-search-input"
        placeholder={placeholder}
        aria-label={placeholder}
        autoComplete="off"
        onChange={(event) => onChange(event.target.value)}
        onKeyDown={(event) => {
            if (event.key === 'Escape') {
                onChange('');
            }
        }}
    />
    {value !== '' && <button type="button" className="fbm-search-clear" onClick={() => onChange('')} title={clearLabel}>
        <Icon name="xmark" /><span className="fbm-sr">{clearLabel}</span>
    </button>}
</div>;

export default SearchBox;
