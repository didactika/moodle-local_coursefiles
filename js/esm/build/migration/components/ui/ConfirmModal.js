import a from"./Icon";import f from"./Modal";import{Fragment as r,jsx as o,jsxs as t}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question that must be answered before something that is hard to undo, in a dialog that takes the focus and closes with Escape.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const g=({open:l,title:s,children:d,confirmLabel:m,cancelLabel:e,onConfirm:c,onCancel:i,tone:b="primary",icon:p="circle-question",busy:n=!1})=>o(f,{open:l,size:"small",busy:n,title:t(r,{children:[o(a,{name:p,gap:!0}),s]}),onClose:i,closeLabel:e,footer:t(r,{children:[o("button",{type:"button",className:"btn btn-outline-secondary",onClick:i,disabled:n,children:e}),t("button",{type:"button",className:`btn btn-${b}`,onClick:c,disabled:n,children:[n&&o(a,{name:"spinner",gap:!0,spin:!0}),m]})]}),children:o("div",{className:"fbm-confirm-body",children:d})});var y=g;export{y as default};
