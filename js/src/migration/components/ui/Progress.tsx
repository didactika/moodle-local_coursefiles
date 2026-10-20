/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A bar that says how much of something is done.
 *
 * @module     local_filebank/migration/components/ui/Progress
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Tone} from './StatTile';

interface ProgressProps {
    value: number;
    max: number;
    label: string;
    tone?: Tone;
}

const Progress = ({value, max, label, tone = 'primary'}: ProgressProps) => {
    const percent = max > 0 ? Math.min(100, Math.round((value / max) * 100)) : 0;
    return <div className="progress" role="progressbar" aria-label={label} aria-valuemin={0} aria-valuemax={max} aria-valuenow={value}>
        <div className={`progress-bar bg-${tone === 'neutral' ? 'secondary' : tone}`} style={{width: `${percent}%`}} />
    </div>;
};

export default Progress;
