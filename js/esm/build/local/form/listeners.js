import o from"@moodle/lms/local_multipicker/local/form/selectors";import{listen as t,eventElement as n}from"@moodle/lms/local_multipicker/local/listeners";/**
 * form listeners.
 * @module local_multipicker/local/form/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const a=e=>{const{wrapper:r}=e;t(r,"input",e.synchronize),t(r,"click",m=>{const l=n(m);if(l.closest(o.addRow)){e.addRow();return}const c=l.closest(o.removeRow);c&&e.removeRow(c.closest(o.tableRow))})},i=e=>{t(e.mode,"change",e.update)};export{i as registerProfile,a as registerRows};
