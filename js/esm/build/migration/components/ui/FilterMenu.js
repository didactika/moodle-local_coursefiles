import{useEffect as u,useRef as f}from"react";import m from"./Icon";import{jsx as n,jsxs as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The filters of a list: a button with the filter icon and the number of filters that are on floating over its corner, and a
 * drop-down with a title, a button that resets them and the choices of each filter as chips. A choice applies as soon as it is
 * made. It is the same drop-down that the pages drawn by the server use.
 *
 * @module     local_filebank/migration/components/ui/FilterMenu
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const b=({label:c,resetLabel:o,groups:d})=>{const s=f(null),l=d.filter(e=>e.value!=="");return u(()=>{const e=i=>{const r=s.current;r?.open&&(!(i.target instanceof Node)||!r.contains(i.target))&&(r.open=!1)},t=i=>{i.key==="Escape"&&s.current&&(s.current.open=!1)};return document.addEventListener("click",e),document.addEventListener("keydown",t),()=>{document.removeEventListener("click",e),document.removeEventListener("keydown",t)}},[]),a("details",{ref:s,className:"fbm-filterbox","data-region":"filters","data-active":l.length>0?"true":void 0,children:[a("summary",{className:"btn btn-outline-secondary",children:[n(m,{name:"filter",gap:!0}),c,l.length>0&&n("span",{className:"badge rounded-pill fbm-count",children:l.length})]}),a("div",{className:"fbm-filterpanel",children:[a("div",{className:"fbm-filter-head",children:[n("h3",{className:"fbm-filter-heading",children:c}),a("button",{type:"button",className:"btn btn-link btn-icon",title:o,disabled:l.length===0,onClick:()=>l.forEach(e=>e.onChange("")),children:[n(m,{name:"rotate-right"}),n("span",{className:"filebank-visually-hidden",children:o})]})]}),d.map(e=>a("fieldset",{className:"fbm-filter-group",children:[n("legend",{className:"fbm-filter-title",children:e.label}),n("div",{className:"fbm-filter-chips",children:e.options.map(t=>a("button",{type:"button",className:`btn btn-sm btn-outline-secondary fbm-chip${e.value===t.value?" active":""}`,"aria-pressed":e.value===t.value,onClick:()=>e.onChange(t.value),children:[t.label,t.count!==void 0&&n("span",{className:"small",children:t.count})]},t.value))})]},e.id))]})]})};var g=b;export{g as default};
