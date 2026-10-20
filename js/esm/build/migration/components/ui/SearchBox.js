import r from"./Icon";import{jsx as a,jsxs as t}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A search field that filters as the person types, with a way to clear it.
 *
 * @module     local_filebank/migration/components/ui/SearchBox
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=({value:n,onChange:e,placeholder:o,clearLabel:l,id:i})=>t("div",{className:"fbm-search",children:[a("span",{className:"fbm-search-icon",children:a(r,{name:"magnifying-glass"})}),a("input",{type:"search",id:i,value:n,className:"form-control rounded-pill",placeholder:o,"aria-label":o,autoComplete:"off",onChange:s=>e(s.target.value),onKeyDown:s=>{s.key==="Escape"&&e("")}}),n!==""&&t("button",{type:"button",className:"fbm-search-clear",onClick:()=>e(""),title:l,children:[a(r,{name:"xmark"}),a("span",{className:"filebank-visually-hidden",children:l})]})]});var p=c;export{p as default};
