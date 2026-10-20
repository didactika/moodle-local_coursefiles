import{useEffect as b,useRef as g}from"react";import u from"./Icon";import{jsx as o,jsxs as l}from"react/jsx-runtime";/**
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
 */const N=({open:a,title:m,onClose:n,closeLabel:t,children:c,footer:s,size:f="medium",busy:d=!1})=>{const i=g(null);b(()=>{const e=i.current;e&&(a&&!e.open?e.showModal():!a&&e.open&&e.close())},[a]);const r=()=>{d||n()};return o("dialog",{ref:i,className:`fbm-dialog fbm-dialog-${f}`,onCancel:e=>{e.preventDefault()},children:a&&l("div",{className:"fbm-dialog-body",children:[l("header",{className:"fbm-dialog-head",children:[o("h3",{className:"fbm-dialog-title",children:m}),l("button",{type:"button",className:"fbm-iconbtn",onClick:r,title:t,disabled:d,children:[o(u,{name:"xmark"}),o("span",{className:"fbm-sr",children:t})]})]}),o("div",{className:"fbm-dialog-scroll",children:c}),s&&o("footer",{className:"fbm-dialog-foot",children:s})]})})};var v=N;export{v as default};
