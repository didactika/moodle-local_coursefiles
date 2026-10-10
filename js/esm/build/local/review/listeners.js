import{listen as l}from"@moodle/lms/local_coursefiles/local/listeners";/**
 * Bind feature events to scoped actions without owning business state.
 *
 * @module local_coursefiles/local/review/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i=(e,t)=>{l(e,"change",t.selectionChange),l(e,"click",t.selectFile),l(e,"click",t.renameFile)};export{i as registerListeners};
