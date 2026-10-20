import{useState as c}from"react";import l from"../ui/Icon";import{jsx as o,jsxs as s}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the migration left out because it did not match: a course without a folder in the source, a destination that has no course
 * for the filters, a hidden folder. None of it is an error, so it is only said, and a person can open it or leave it closed.
 *
 * @module     local_filebank/migration/components/review/Notes
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=({notes:n,text:a})=>{const[e,m]=c(!1);if(n.length===0)return null;const r=n.reduce((t,i)=>t+i.count,0);return s("div",{className:"fbm-notes-box","data-region":"notes",children:[s("button",{type:"button",className:"fbm-notes-toggle","aria-expanded":e,onClick:()=>m(!e),children:[o(l,{name:"circle-info",gap:!0}),a("notes_title",{n:r}),o(l,{name:e?"chevron-up":"chevron-down"})]}),e&&s("ul",{className:"fbm-notes",children:[n.map(t=>o("li",{children:t.label},t.kind)),o("li",{className:"fbm-muted",children:a("notes_footer")})]})]})};var d=p;export{d as default};
