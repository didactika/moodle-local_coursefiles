import m from"../ui/Alert";import c from"../ui/ConfirmModal";import{jsx as r,jsxs as n}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The last question before a migration writes anything: what it will do, with the files that overwrite another one said apart,
 * and what it will leave as it is.
 *
 * @module     local_filebank/migration/components/review/StartModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=({open:i,migration:a,text:o,busy:e,onConfirm:l,onCancel:s})=>{const{summary:t}=a;return n(c,{open:i,icon:"play",busy:e,title:o("start_title",{n:a.id}),confirmLabel:o("start"),cancelLabel:o("cancel"),onConfirm:l,onCancel:s,children:[n("ul",{className:"fbm-confirm-list",children:[n("li",{children:[r("strong",{children:t.tocopy})," ",o("start_copy")]}),t.tooverwrite>0&&n("li",{className:"is-warning",children:[r("strong",{children:t.tooverwrite})," ",o("start_overwrite")]}),n("li",{className:"text-muted",children:[r("strong",{children:t.existing})," ",o("start_existing")]})]}),t.tooverwrite>0&&r(m,{tone:"warning",children:o("start_overwrite_warning")}),r("p",{className:"text-muted",children:o("start_background")})]})};var f=p;export{f as default};
