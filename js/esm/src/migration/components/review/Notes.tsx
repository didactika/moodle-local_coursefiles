/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the migration left out because it did not match: a course without a folder in the source, a destination that has no course
 * for the filters, a hidden folder. None of it is an error, so it is only said, and a person can open it or leave it closed.
 *
 * @module     local_filebank/migration/components/review/Notes
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useState} from 'react';
import type {Summary, Text} from '../../types';
import Icon from '../ui/Icon';

interface NotesProps {
    notes: Summary['notes'];
    text: Text;
}

const Notes = ({notes, text}: NotesProps) => {
    const [open, setOpen] = useState(false);
    if (notes.length === 0) {
        return null;
    }
    const total = notes.reduce((sum, note) => sum + note.count, 0);
    return <div className="fbm-notes-box" data-region="notes">
        <button type="button" className="fbm-notes-toggle" aria-expanded={open} onClick={() => setOpen(!open)}>
            <Icon name="circle-info" gap />
            {text('notes_title', {n: total})}
            <Icon name={open ? 'chevron-up' : 'chevron-down'} />
        </button>
        {open && <ul className="fbm-notes">
            {notes.map((note) => <li key={note.kind}>{note.label}</li>)}
            <li className="fbm-muted">{text('notes_footer')}</li>
        </ul>}
    </div>;
};

export default Notes;
