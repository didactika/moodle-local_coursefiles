/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The badge of a state, with its icon and its text, in the colour of what it says.
 *
 * @module     local_filebank/migration/components/ui/StatusBadge
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {badgeClasses} from '../../lib/tones';
import Icon from './Icon';
import type {Tone} from './StatTile';

interface Look {
    tone: Tone;
    icon: string;
}

const LOOKS: Record<string, Look> = {
    reviewable: {tone: 'info', icon: 'clipboard-check'},
    queued: {tone: 'warning', icon: 'clock'},
    running: {tone: 'primary', icon: 'spinner'},
    partial: {tone: 'warning', icon: 'triangle-exclamation'},
    completed: {tone: 'success', icon: 'circle-check'},
    cancelled: {tone: 'neutral', icon: 'ban'},
    blocked: {tone: 'danger', icon: 'circle-xmark'},
    copy: {tone: 'success', icon: 'copy'},
    overwrite: {tone: 'warning', icon: 'pen-to-square'},
    skip_identical: {tone: 'neutral', icon: 'equals'},
    skip_content: {tone: 'neutral', icon: 'equals'},
    skip_conflict: {tone: 'neutral', icon: 'hand'},
    pending: {tone: 'neutral', icon: 'hourglass-half'},
    copied: {tone: 'success', icon: 'circle-check'},
    done: {tone: 'success', icon: 'circle-check'},
};

/**
 * How a state looks.
 *
 * @param status Code of the state or of the decision.
 * @returns Its colour and icon; the ones of something that has to be looked at when it is not known.
 */
const lookOf = (status: string): Look => {
    if (status.startsWith('blocked')) {
        return {tone: 'danger', icon: 'circle-exclamation'};
    }
    return LOOKS[status] ?? {tone: 'neutral', icon: 'circle'};
};

interface StatusBadgeProps {
    status: string;
    label: string;
}

const StatusBadge = ({status, label}: StatusBadgeProps) => {
    const look = lookOf(status);
    return <span className={badgeClasses(look.tone)}>
        <Icon name={look.icon} gap spin={status === 'running'} />{label}
    </span>;
};

export default StatusBadge;
