import{listen as i}from"@moodle/lms/local_multipicker/local/listeners";/**
 * Bind feature events to scoped actions without owning business state.
 *
 * @module local_multipicker/local/migration/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const n=(e,t)=>{i(e,"change",t.selectionChange),i(e,"submit",t.submit),i(e,"input",t.filterInput),i(e,"click",t.navigate)};export{n as registerListeners};
