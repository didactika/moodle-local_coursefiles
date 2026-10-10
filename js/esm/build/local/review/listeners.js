import{listen as i}from"@moodle/lms/local_multipicker/local/listeners";/**
 * Bind feature events to scoped actions without owning business state.
 *
 * @module local_multipicker/local/review/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=(e,t)=>{i(e,"change",t.selectionChange),i(e,"click",t.selectFile),i(e,"click",t.renameFile)};export{c as registerListeners};
