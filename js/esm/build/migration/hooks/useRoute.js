import{useCallback as d,useEffect as a,useState as c}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which screen is open, kept in the address so that a migration can be linked to and the back button of the browser works.
 *
 * @module     local_filebank/migration/hooks/useRoute
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const w=(t,n)=>{const[s,o]=c(t);a(()=>{const e=()=>o(Number(new URLSearchParams(window.location.search).get("id"))||0);return window.addEventListener("popstate",e),()=>window.removeEventListener("popstate",e)},[]);const i=d(e=>{const r=e>0?`${n}${n.includes("?")?"&":"?"}id=${e}`:n;window.history.pushState({},"",r),o(e),window.scrollTo({top:0})},[n]);return{planid:s,open:i}};export{w as useRoute};
