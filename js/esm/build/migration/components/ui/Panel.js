import m from"./Icon";import{jsx as e,jsxs as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A section of a page: a card with a title, an icon, how many things it holds and, when it has them, its own actions.
 *
 * @module     local_filebank/migration/components/ui/Panel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=({title:l,icon:o,count:n,actions:s,flush:t=!1,region:c,children:i})=>a("section",{className:"fbm-panel","data-region":c,children:[a("header",{className:"fbm-panel-head",children:[a("h3",{className:"fbm-panel-title",children:[e("span",{className:"fbm-panel-icon",children:e(m,{name:o,gap:!0})}),l,n!==void 0&&e("span",{className:"fbm-count",children:n})]}),s&&e("div",{className:"fbm-panel-actions",children:s})]}),e("div",{className:t?"fbm-panel-flush":"fbm-panel-body",children:i})]});var f=p;export{f as default};
