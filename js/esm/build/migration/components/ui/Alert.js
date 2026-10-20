import c from"./Icon";import{jsx as e,jsxs as n}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A message inside the page: something that went wrong, something that is good to know.
 *
 * @module     local_filebank/migration/components/ui/Alert
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i={danger:"circle-exclamation",warning:"triangle-exclamation",info:"circle-info",success:"circle-check",neutral:"circle-info",primary:"circle-info"},l=({tone:a="info",children:o,actions:r,live:t=!1})=>n("div",{className:`fbm-alert tone-${a}`,role:t?"alert":"status",children:[e("span",{className:"fbm-alert-icon",children:e(c,{name:i[a]})}),e("div",{className:"fbm-alert-body",children:o}),r&&e("div",{className:"fbm-alert-actions",children:r})]});var m=l;export{m as default};
