import o from"@moodle/lms/local_coursefiles/local/form/selectors";import{listen as t,eventElement as n}from"@moodle/lms/local_coursefiles/local/listeners";/**
 * form listeners.
 * @module local_coursefiles/local/form/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=e=>{const{wrapper:r}=e;t(r,"input",e.synchronize),t(r,"click",c=>{const l=n(c);if(l.closest(o.addRow)){e.addRow();return}const s=l.closest(o.removeRow);s&&e.removeRow(s.closest(o.tableRow))})},i=e=>{t(e.mode,"change",e.update)};export{i as registerProfile,p as registerRows};
