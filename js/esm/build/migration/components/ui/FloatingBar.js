import i from"./Icon";import{jsx as a,jsxs as o}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What can be done with what was chosen. It floats at the bottom of the screen while there is a choice, so the page does not move
 * and the actions are always at hand.
 *
 * @module     local_filebank/migration/components/ui/FloatingBar
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const l=({summary:n,children:s,onClear:e,clearLabel:t})=>o("div",{className:"fbm-floating",role:"status","data-region":"bulk-actions",children:[a("span",{className:"fbm-floating-summary",children:n}),a("div",{className:"fbm-floating-actions",children:s}),o("button",{type:"button",className:"fbm-floating-clear",onClick:e,title:t,children:[a(i,{name:"xmark"}),a("span",{className:"fbm-sr",children:t})]})]});var m=l;export{m as default};
