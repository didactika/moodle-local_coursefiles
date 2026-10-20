/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A switch that turns something on or off, which a person tells from a check box because it takes effect at once.
 *
 * @module     local_filebank/migration/components/ui/Switch
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

interface SwitchProps {
    checked: boolean;
    /** Some of what it covers is on and some is not. */
    partial?: boolean;
    label: string;
    onChange: (checked: boolean) => void;
    disabled?: boolean;
}

const Switch = ({checked, partial = false, label, onChange, disabled = false}: SwitchProps) => <button
    type="button"
    role="switch"
    aria-checked={partial ? 'mixed' : checked}
    aria-label={label}
    title={label}
    className={`fbm-switch${checked ? ' is-on' : ''}${partial ? ' is-partial' : ''}`}
    disabled={disabled}
    onClick={() => onChange(partial ? true : !checked)}
>
    <span className="fbm-switch-knob" />
</button>;

export default Switch;
