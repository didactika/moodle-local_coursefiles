import o from"./Icon";import{jsx as e,jsxs as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A section of a page: a card with a title, an icon, how many things it holds and, when it has them, its own actions.
 *
 * @module     local_filebank/migration/components/ui/Panel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const r=({title:t,icon:c,count:n,actions:s,flush:d=!1,region:i,children:l})=>a("section",{className:"card","data-region":i,children:[a("header",{className:"card-header d-flex flex-wrap align-items-center justify-content-between",children:[a("h3",{className:"card-title h5 mb-0 d-flex align-items-center",children:[e("span",{className:"fbm-panel-icon",children:e(o,{name:c,gap:!0})}),t,n!==void 0&&e("span",{className:"badge rounded-pill fbm-count",children:n})]}),s&&e("div",{className:"fbm-panel-actions",children:s})]}),e("div",{className:d?"card-body p-0":"card-body",children:l})]});var p=r;export{p as default};
