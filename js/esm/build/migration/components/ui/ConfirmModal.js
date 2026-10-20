import a from"./Icon";import p from"./Modal";import{Fragment as r,jsx as o,jsxs as t}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question that must be answered before something that is hard to undo, in a dialog that takes the focus and closes with Escape.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const g=({open:l,title:m,children:s,confirmLabel:b,cancelLabel:e,onConfirm:d,onCancel:i,tone:c="primary",icon:f="circle-question",busy:n=!1})=>o(p,{open:l,size:"small",busy:n,title:t(r,{children:[o(a,{name:f,gap:!0}),m]}),onClose:i,closeLabel:e,footer:t(r,{children:[o("button",{type:"button",className:"fbm-btn",onClick:i,disabled:n,children:e}),t("button",{type:"button",className:`fbm-btn fbm-btn-${c}`,onClick:d,disabled:n,children:[n&&o(a,{name:"spinner",gap:!0,spin:!0}),b]})]}),children:o("div",{className:"fbm-confirm-body",children:s})});var y=g;export{y as default};
