import{badgeClasses as c}from"../../lib/tones";import t from"./Icon";import{jsx as s,jsxs as l}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The badge of a state, with its icon and its text, in the colour of what it says.
 *
 * @module     local_filebank/migration/components/ui/StatusBadge
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i={reviewable:{tone:"info",icon:"clipboard-check"},queued:{tone:"warning",icon:"clock"},running:{tone:"primary",icon:"spinner"},partial:{tone:"warning",icon:"triangle-exclamation"},completed:{tone:"success",icon:"circle-check"},cancelled:{tone:"neutral",icon:"ban"},blocked:{tone:"danger",icon:"circle-xmark"},copy:{tone:"success",icon:"copy"},overwrite:{tone:"warning",icon:"pen-to-square"},skip_identical:{tone:"neutral",icon:"equals"},skip_content:{tone:"neutral",icon:"equals"},skip_conflict:{tone:"neutral",icon:"hand"},pending:{tone:"neutral",icon:"hourglass-half"},copied:{tone:"success",icon:"circle-check"},done:{tone:"success",icon:"circle-check"}},r=n=>n.startsWith("blocked")?{tone:"danger",icon:"circle-exclamation"}:i[n]??{tone:"neutral",icon:"circle"},a=({status:n,label:o})=>{const e=r(n);return l("span",{className:c(e.tone),children:[s(t,{name:e.icon,gap:!0,spin:n==="running"}),o]})};var g=a;export{g as default};
