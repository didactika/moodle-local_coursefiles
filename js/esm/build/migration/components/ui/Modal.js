import{useEffect as b,useRef as u}from"react";import g from"./Icon";import{jsx as o,jsxs as l}from"react/jsx-runtime";/**
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
 */const N=({open:a,title:n,onClose:m,closeLabel:t,children:c,footer:d,size:f="medium",busy:s=!1})=>{const i=u(null);b(()=>{const e=i.current;e&&(a&&!e.open?e.showModal():!a&&e.open&&e.close())},[a]);const r=()=>{s||m()};return o("dialog",{ref:i,className:`fbm-dialog fbm-dialog-${f}`,onCancel:e=>{e.preventDefault()},children:a&&l("div",{className:"modal-content fbm-dialog-body",children:[l("header",{className:"modal-header fbm-dialog-head",children:[o("h3",{className:"modal-title h5",children:n}),l("button",{type:"button",className:"btn btn-link btn-icon",onClick:r,title:t,disabled:s,children:[o(g,{name:"xmark"}),o("span",{className:"filebank-visually-hidden",children:t})]})]}),o("div",{className:"modal-body fbm-dialog-scroll",children:c}),d&&o("footer",{className:"modal-footer fbm-dialog-foot",children:d})]})})};var v=N;export{v as default};
