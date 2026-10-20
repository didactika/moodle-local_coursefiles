import m from"../ui/Alert";import c from"../ui/ConfirmModal";import{jsx as t,jsxs as n}from"react/jsx-runtime";/**
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
 */const p=({open:i,migration:a,text:o,busy:s,onConfirm:e,onCancel:l})=>{const{summary:r}=a;return n(c,{open:i,icon:"play",busy:s,title:o("start_title",{n:a.id}),confirmLabel:o("start"),cancelLabel:o("cancel"),onConfirm:e,onCancel:l,children:[n("ul",{className:"fbm-confirm-list",children:[n("li",{children:[t("strong",{children:r.tocopy})," ",o("start_copy")]}),r.tooverwrite>0&&n("li",{className:"is-warning",children:[t("strong",{children:r.tooverwrite})," ",o("start_overwrite")]}),n("li",{className:"fbm-muted",children:[t("strong",{children:r.existing})," ",o("start_existing")]})]}),r.tooverwrite>0&&t(m,{tone:"warning",children:o("start_overwrite_warning")}),r.conflicts+r.openissues>0&&t("p",{className:"fbm-muted",children:o("start_left",{n:r.conflicts+r.openissues})}),t("p",{className:"fbm-muted",children:o("start_background")})]})};var d=p;export{d as default};
