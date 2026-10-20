import m from"./Icon";import{Fragment as f,jsx as a,jsxs as i}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A figure with its label: how many files would be copied, how many wait for a decision. It is a button when the figure leads
 * somewhere, such as the list of the things it counts.
 *
 * @module     local_filebank/migration/components/ui/StatTile
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const b=({label:p,value:c,tone:r="neutral",icon:t,hint:s,onClick:e,active:n=!1})=>{const l=i(f,{children:[i("span",{className:"fbm-tile-top",children:[a("span",{className:"fbm-tile-label",children:p}),t&&a("span",{className:"fbm-tile-icon",children:a(m,{name:t})})]}),a("span",{className:"fbm-tile-value",children:c}),s&&a("span",{className:"fbm-tile-hint",children:s})]}),o=`fbm-tile tone-${r}${n?" is-active":""}${e?" is-link":""}`;return e?a("button",{type:"button",className:o,onClick:e,"aria-pressed":n,children:l}):a("div",{className:o,children:l})};var d=b;export{d as default};
