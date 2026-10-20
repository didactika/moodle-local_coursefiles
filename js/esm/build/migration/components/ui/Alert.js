import i from"./Icon";import{alertClasses as c}from"../../lib/tones";import{jsx as e,jsxs as s}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A message inside the page: something that went wrong, something that is good to know.
 *
 * @module     local_filebank/migration/components/ui/Alert
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const l={danger:"circle-exclamation",warning:"triangle-exclamation",info:"circle-info",success:"circle-check",neutral:"circle-info",primary:"circle-info"},n=({tone:a="info",children:o,actions:r,live:t=!1})=>s("div",{className:`${c(a)} fbm-alert`,role:t?"alert":"status",children:[e("span",{className:"fbm-alert-icon",children:e(i,{name:l[a]})}),e("div",{className:"fbm-alert-body",children:o}),r&&e("div",{className:"fbm-alert-actions",children:r})]});var d=n;export{d as default};
