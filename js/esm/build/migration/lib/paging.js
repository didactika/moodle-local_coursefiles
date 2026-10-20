/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which pages a bar of pages shows around the one that is open.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const b=(o,s,r,n=2)=>{const e=Math.max(1,Math.ceil(r/Math.max(1,s))),h=Math.max(0,Math.min(o-n,e-1-n*2)),m=Math.min(e-1,Math.max(o+n,n*2)),t=[];for(let a=Math.max(0,h);a<=m;a++)t.push(a);return{pages:e,shown:t,first:t[0]>0,last:t[t.length-1]<e-1,startDots:t[0]>1,endDots:t[t.length-1]<e-2}};export{b as pageWindow};
