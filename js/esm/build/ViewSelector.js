import{useEffect as c,useRef as i,useState as f}from"react";import{normalizeLayout as a}from"./layout";import{Fragment as y,jsx as o,jsxs as m}from"react/jsx-runtime";/**
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_coursefiles/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function d({initialLayout:s,views:l}){const[r,u]=f(a(s)),n=i(null);return c(()=>{const e=n.current?.closest("form.coursefiles-browser");if(!e)return;const t=()=>u(a(e.dataset.layout||s));return t(),e.addEventListener("coursefiles:layoutchanged",t),()=>e.removeEventListener("coursefiles:layoutchanged",t)},[s]),o(y,{children:l.map((e,t)=>m("button",{ref:t===0?n:void 0,type:"button",className:`btn btn-sm btn-outline-secondary${r===e.value?" active":""}`,"data-coursefiles-layout":e.value,"data-coursefiles-react-view":"true","aria-pressed":r===e.value,title:e.label,children:[o("span",{dangerouslySetInnerHTML:{__html:e.icon}}),o("span",{className:"coursefiles-visually-hidden",children:e.label})]},e.value))})}export{d as default};
