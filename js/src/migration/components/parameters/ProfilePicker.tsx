/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration. A site can have many profiles, so the list never shows more than ten: the first ones
 * by name, or the ones that match what is typed in the search above it.
 *
 * @module     local_filebank/migration/components/parameters/ProfilePicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState} from 'react';
import {useDebounced} from '../../hooks/useDebounced';
import type {ProfileOption, Text} from '../../types';
import Icon from '../ui/Icon';

interface ProfilePickerProps {
    profiles: ProfileOption[];
    chosen: number;
    /** The profile cannot be changed, because the migration already exists. */
    locked: boolean;
    invalid: boolean;
    onPick: (profile: ProfileOption) => void;
    /** Ask for the profiles whose name has the text. */
    onSearch: (query: string) => void;
    text: Text;
}

const ProfilePicker = ({profiles, chosen, locked, invalid, onPick, onSearch, text}: ProfilePickerProps) => {
    const [query, setQuery] = useState('');
    const followed = useDebounced(query, 300);
    const first = useRef(true);

    useEffect(() => {
        // The first render has nothing to look for: the options already came with the page.
        if (first.current) {
            first.current = false;
            return;
        }
        onSearch(followed.trim());
    }, [followed]);

    return <div className={`fbm-picker${invalid ? ' has-problem' : ''}`} data-region="profile-picker">
        {!locked && <div className="fbm-search">
            <span className="fbm-search-icon"><Icon name="magnifying-glass" /></span>
            <input
                type="search"
                className="form-control rounded-pill"
                value={query}
                placeholder={text('profile_search')}
                aria-label={text('profile_search')}
                autoComplete="off"
                onChange={(event) => setQuery(event.target.value)}
            />
        </div>}
        <div className="list-group fbm-picker-list" role="listbox" aria-label={text('wizard_profile')}>
            {profiles.map((profile) => <button
                key={profile.id}
                type="button"
                role="option"
                aria-selected={profile.id === chosen}
                className={`list-group-item list-group-item-action${profile.id === chosen ? ' active' : ''}`}
                disabled={locked && profile.id !== chosen}
                onClick={() => onPick(profile)}
            >
                {profile.id === chosen && <Icon name="check" gap />}{profile.name}
            </button>)}
            {profiles.length === 0 && <p className="text-muted fbm-hint">{text('profile_none')}</p>}
        </div>
    </div>;
};

export default ProfilePicker;
