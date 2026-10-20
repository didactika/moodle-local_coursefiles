/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What went wrong in a call, as a sentence the person can read.
 *
 * @module     local_filebank/migration/lib/errors
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=e=>{if(typeof e=="string")return e;if(e!==null&&typeof e=="object"){const r=e;return r.message??r.error??r.errorcode??""}return""},n=e=>e!==null&&typeof e=="object"?e.errorcode==="planstale":!1;export{o as describeError,n as isStale};
