import{useEffect as i,useRef as c,useState as f}from"react";import{normalizeLayout as l}from"./layout";import{Fragment as d,jsx as n,jsxs as b}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_filebank/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function m({initialLayout:a,views:s}){const[o,u]=f(l(a)),r=c(null);return i(()=>{const e=r.current?.closest("form.filebank-browser");if(!e)return;const t=()=>u(l(e.dataset.layout||a));return t(),e.addEventListener("filebank:layoutchanged",t),()=>e.removeEventListener("filebank:layoutchanged",t)},[a]),n(d,{children:s.map((e,t)=>b("button",{ref:t===0?r:void 0,type:"button",className:`fbm-segment-button${o===e.value?" active":""}`,"data-filebank-layout":e.value,"data-filebank-react-view":"true","aria-pressed":o===e.value,title:e.label,children:[n("span",{dangerouslySetInnerHTML:{__html:e.icon}}),n("span",{className:"filebank-visually-hidden",children:e.label})]},e.value))})}export{m as default};
