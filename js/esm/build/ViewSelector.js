import{useState as n}from"react";import{normalizeLayout as r}from"./layout";import{Fragment as c,jsx as e,jsxs as s}from"react/jsx-runtime";/**
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_multipicker/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function u({initialLayout:l,views:o}){const[a,i]=n(r(l));return e(c,{children:o.map(t=>s("button",{type:"button",className:`btn btn-sm btn-outline-secondary${a===t.value?" active":""}`,"data-multipicker-layout":t.value,"data-multipicker-react-view":"true","aria-pressed":a===t.value,title:t.label,onClick:()=>i(t.value),children:[e("span",{dangerouslySetInnerHTML:{__html:t.icon}}),e("span",{className:"multipicker-visually-hidden",children:t.label})]},t.value))})}export{u as default};
