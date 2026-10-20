import{jsx as n,jsxs as i}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A row of choices of which one is on, drawn as pills: the groups of files, the size of a page.
 *
 * @module     local_filebank/migration/components/ui/Chips
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=({label:t,value:a,chips:s,onChange:l})=>n("div",{className:"fbm-chips",role:"group","aria-label":t,children:s.map(e=>i("button",{type:"button",className:`btn btn-sm btn-outline-secondary fbm-chip${e.value===a?" active":""}`,"aria-pressed":e.value===a,onClick:()=>l(e.value),children:[e.label,e.count!==void 0&&n("span",{className:"badge rounded-pill fbm-chip-count",children:e.count})]},e.value))});var u=o;export{u as default};
