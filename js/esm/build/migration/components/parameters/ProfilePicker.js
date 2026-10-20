import{useEffect as b,useRef as g,useState as v}from"react";import{useDebounced as P}from"../../hooks/useDebounced";import m from"../ui/Icon";import{jsx as i,jsxs as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration. A site can have many profiles, so the list never shows more than ten: the first ones
 * by name, or the ones that match what is typed in the search above it.
 *
 * @module     local_filebank/migration/components/parameters/ProfilePicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const h=({profiles:t,chosen:o,locked:l,invalid:p,onPick:f,onSearch:d,text:r})=>{const[s,u]=v(""),n=P(s,300),c=g(!0);return b(()=>{if(c.current){c.current=!1;return}d(n.trim())},[n]),a("div",{className:`fbm-picker${p?" has-problem":""}`,"data-region":"profile-picker",children:[!l&&a("div",{className:"fbm-search",children:[i("span",{className:"fbm-search-icon",children:i(m,{name:"magnifying-glass"})}),i("input",{type:"search",className:"form-control rounded-pill",value:s,placeholder:r("profile_search"),"aria-label":r("profile_search"),autoComplete:"off",onChange:e=>u(e.target.value)})]}),a("div",{className:"list-group fbm-picker-list",role:"listbox","aria-label":r("wizard_profile"),children:[t.map(e=>a("button",{type:"button",role:"option","aria-selected":e.id===o,className:`list-group-item list-group-item-action${e.id===o?" active":""}`,disabled:l&&e.id!==o,onClick:()=>f(e),children:[e.id===o&&i(m,{name:"check",gap:!0}),e.name]},e.id)),t.length===0&&i("p",{className:"text-muted fbm-hint",children:r("profile_none")})]})]})};var _=h;export{_ as default};
