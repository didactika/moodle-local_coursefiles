import{listen as e}from"@moodle/lms/local_multipicker/local/listeners";import i from"@moodle/lms/local_multipicker/local/selection/selectors";/**
 * Bind feature events to scoped actions without owning business state.
 *
 * @module local_multipicker/local/selection/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=(t,l)=>{e(document,"click",l.outsideClick),e(t,"keydown",l.escapeFilters),e(t,"click",l.openFolder),e(t,"click",l.clearFilters),e(t,"click",l.toggleCourse),e(t,"input",l.filterInput),e(t,"change",l.selectionChange),e(t,"click",l.selectCard),e(t,"submit",l.submit),e(t,"click",l.changeLayout),t.querySelectorAll(i.options).forEach(c=>{const o="multipicker-options";try{c.open=sessionStorage.getItem(o)==="open"}catch{}e(c,"toggle",()=>{try{sessionStorage.setItem(o,c.open?"open":"closed")}catch{}})})};export{p as registerListeners};
