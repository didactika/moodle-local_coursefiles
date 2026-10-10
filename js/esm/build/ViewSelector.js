import{useEffect as i,useRef as c,useState as m}from"react";import{normalizeLayout as l}from"./layout";import{Fragment as f,jsx as n,jsxs as d}from"react/jsx-runtime";/**
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_multipicker/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function p({initialLayout:r,views:u}){const[a,s]=m(l(r)),o=c(null);return i(()=>{const e=o.current?.closest("form.multipicker-browser");if(!e)return;const t=()=>s(l(e.dataset.layout||r));return t(),e.addEventListener("multipicker:layoutchanged",t),()=>e.removeEventListener("multipicker:layoutchanged",t)},[r]),n(f,{children:u.map((e,t)=>d("button",{ref:t===0?o:void 0,type:"button",className:`btn btn-sm btn-outline-secondary${a===e.value?" active":""}`,"data-multipicker-layout":e.value,"data-multipicker-react-view":"true","aria-pressed":a===e.value,title:e.label,children:[n("span",{dangerouslySetInnerHTML:{__html:e.icon}}),n("span",{className:"multipicker-visually-hidden",children:e.label})]},e.value))})}export{p as default};
