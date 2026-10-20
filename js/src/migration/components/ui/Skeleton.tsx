/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Grey blocks that hold the place of what is being read, so the page does not jump when it arrives.
 *
 * @module     local_filebank/migration/components/ui/Skeleton
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

interface SkeletonProps {
    /** How many rows to draw. */
    rows?: number;
    label: string;
}

const Skeleton = ({rows = 4, label}: SkeletonProps) => <div className="fbm-skeleton" role="status" aria-label={label}>
    {Array.from({length: rows}, (_, index) => <span key={index} className="fbm-skeleton-row" />)}
</div>;

export default Skeleton;
