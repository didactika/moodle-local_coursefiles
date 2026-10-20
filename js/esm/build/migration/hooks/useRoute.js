import{useCallback as a,useEffect as w,useState as c}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which screen is open, kept in the address so that a migration can be linked to and the back button of the browser works.
 *
 * @module     local_filebank/migration/hooks/useRoute
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=t=>{const o=new URLSearchParams(t),n=Number(o.get("id"))||0;return n>0?{mode:o.get("edit")==="1"?"edit":"review",planid:n,from:0}:o.get("new")==="1"?{mode:"new",planid:0,from:Number(o.get("from"))||0}:{mode:"list",planid:0,from:0}},u=(t,o)=>{const[n,r]=c(()=>t>0?{mode:"review",planid:t,from:0}:s(window.location.search));w(()=>{const e=()=>r(s(window.location.search));return window.addEventListener("popstate",e),()=>window.removeEventListener("popstate",e)},[]);const i=a((e,d)=>{const m=d===""?o:`${o}${o.includes("?")?"&":"?"}${d}`;window.history.pushState({},"",m),r(e),window.scrollTo({top:0})},[o]);return{...n,openList:()=>i({mode:"list",planid:0,from:0},""),openNew:(e=0)=>i({mode:"new",planid:0,from:e},e>0?`new=1&from=${e}`:"new=1"),openReview:e=>i({mode:"review",planid:e,from:0},`id=${e}`),openEdit:e=>i({mode:"edit",planid:e,from:0},`id=${e}&edit=1`)}};export{u as useRoute};
