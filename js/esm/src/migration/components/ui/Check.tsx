/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A check box that can also say that some of what it covers is checked.
 *
 * @module     local_filebank/migration/components/ui/Check
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef} from 'react';

interface CheckProps {
    checked: boolean;
    partial?: boolean;
    label: string;
    onChange: (checked: boolean) => void;
    disabled?: boolean;
}

const Check = ({checked, partial = false, label, onChange, disabled = false}: CheckProps) => {
    const input = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (input.current) {
            input.current.indeterminate = partial && !checked;
        }
    }, [partial, checked]);

    return <input
        ref={input}
        type="checkbox"
        className="fbm-check"
        checked={checked}
        disabled={disabled}
        aria-label={label}
        title={label}
        onChange={(event) => onChange(event.target.checked)}
    />;
};

export default Check;
