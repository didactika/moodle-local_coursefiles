import{useEffect as b,useRef as g}from"react";import u from"./Icon";import{jsx as o,jsxs as s}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A window over the page, in a dialog of the browser: it takes the focus, closes with Escape or with its button and gives the focus
 * back to where the person was.
 *
 * @module     local_filebank/migration/components/ui/Modal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const N=({open:a,title:m,onClose:c,closeLabel:i,children:f,footer:d,size:r="medium",busy:n=!1})=>{const l=g(null);b(()=>{const e=l.current;e&&(a&&!e.open?e.showModal():!a&&e.open&&e.close())},[a]);const t=()=>{n||c()};return o("dialog",{ref:l,className:`fbm-dialog fbm-dialog-${r}`,onCancel:e=>{e.preventDefault(),t()},onClick:e=>{e.target===l.current&&t()},children:a&&s("div",{className:"fbm-dialog-body",children:[s("header",{className:"fbm-dialog-head",children:[o("h3",{className:"fbm-dialog-title",children:m}),s("button",{type:"button",className:"fbm-iconbtn",onClick:t,title:i,disabled:n,children:[o(u,{name:"xmark"}),o("span",{className:"fbm-sr",children:i})]})]}),o("div",{className:"fbm-dialog-scroll",children:f}),d&&o("footer",{className:"fbm-dialog-foot",children:d})]})})};var v=N;export{v as default};
