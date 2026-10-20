/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Text of the page, which the server sends once with the properties of the application.
 *
 * @module     local_filebank/migration/lib/text
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=i=>(r,t="")=>{const e=i[r]??r;return t!==null&&typeof t=="object"?Object.keys(t).reduce((o,n)=>o.split(`{${n}}`).join(String(t[n])),e):e.split("{n}").join(String(t))};export{s as createText};
