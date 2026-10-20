/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration: a search and the profiles as cards to choose from, each with what it recognises. A site
 * can have many profiles, so never more than ten are shown: the first ones by name, or the ones that match the search.
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

/** How many profiles a list of choices shows at most. */
const SHOWN = 10;

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

    const shown = locked ? profiles.filter((profile) => profile.id === chosen) : profiles;

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
        <div className="fbm-choices fbm-profile-choices" role="radiogroup" aria-label={text('wizard_profile')}>
            {shown.map((profile) => <label key={profile.id} className={`fbm-choice${profile.id === chosen ? ' is-on' : ''}`}>
                <input
                    type="radio"
                    name="fbm-profile"
                    value={profile.id}
                    checked={profile.id === chosen}
                    disabled={locked}
                    onChange={() => onPick(profile)}
                />
                <span className="fbm-choice-dot" aria-hidden="true" />
                <span className="fbm-choice-text">
                    <span className="fbm-choice-title">{profile.name}</span>
                    <span className="fbm-choice-hint">
                        {profile.dimensions.length > 0
                            ? profile.dimensions.map((dimension) => dimension.label).join(' · ')
                            : text('profile_no_filters')}
                    </span>
                </span>
            </label>)}
        </div>
        {shown.length === 0 && <p className="text-muted fbm-hint">{text('profile_none')}</p>}
        {!locked && shown.length >= SHOWN && <p className="text-muted fbm-hint">{text('profile_more', {n: SHOWN})}</p>}
    </div>;
};

export default ProfilePicker;
