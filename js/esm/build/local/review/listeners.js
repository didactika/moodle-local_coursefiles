import{listen as l}from"@moodle/lms/local_filebank/local/listeners";/**
 * Bind feature events to scoped actions without owning business state.
 *
 * @module local_filebank/local/review/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=(e,t)=>{l(e,"change",t.selectionChange),l(e,"click",t.selectFile),l(e,"click",t.renameFile)};export{c as registerListeners};
