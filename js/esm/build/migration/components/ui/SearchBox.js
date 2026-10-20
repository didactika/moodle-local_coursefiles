import o from"./Icon";import{jsx as a,jsxs as i}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A search field that filters as the person types, with a way to clear it.
 *
 * @module     local_filebank/migration/components/ui/SearchBox
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const l=({value:n,onChange:e,placeholder:t,clearLabel:r,id:c})=>i("div",{className:"fbm-search",children:[a("span",{className:"fbm-search-icon",children:a(o,{name:"magnifying-glass"})}),a("input",{type:"search",id:c,value:n,className:"fbm-input fbm-search-input",placeholder:t,"aria-label":t,autoComplete:"off",onChange:s=>e(s.target.value),onKeyDown:s=>{s.key==="Escape"&&e("")}}),n!==""&&i("button",{type:"button",className:"fbm-search-clear",onClick:()=>e(""),title:r,children:[a(o,{name:"xmark"}),a("span",{className:"fbm-sr",children:r})]})]});var p=l;export{p as default};
