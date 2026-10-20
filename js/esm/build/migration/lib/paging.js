/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The sizes a page of a list can have, as block_course_finder offers them: the ones smaller than the list, and the whole list when
 * it is not too long, so a page never loads more than the person asked for and "All" never loads a long list at once.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const n=[12,24,48,96],c=500,i=e=>{if(e<=n[0])return[Math.max(1,e)];const r=n.filter(s=>s<e);return e<=c?[...r,e]:r};export{c as MAX_ALL,n as PAGE_SIZES,i as pageSizeChoices};
