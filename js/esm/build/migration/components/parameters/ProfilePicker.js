import{useEffect as N,useRef as g,useState as v}from"react";import{useDebounced as P}from"../../hooks/useDebounced";import y from"../ui/Icon";import{jsx as a,jsxs as s}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration: a search and the profiles as cards to choose from, each with what it recognises. A site
 * can have many profiles, so never more than ten are shown: the first ones by name, or the ones that match the search.
 *
 * @module     local_filebank/migration/components/parameters/ProfilePicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const f=10,_=({profiles:l,chosen:r,locked:o,invalid:p,onPick:d,onSearch:h,text:i})=>{const[t,u]=v(""),c=P(t,300),m=g(!0);N(()=>{if(m.current){m.current=!1;return}h(c.trim())},[c]);const n=o?l.filter(e=>e.id===r):l;return s("div",{className:`fbm-picker${p?" has-problem":""}`,"data-region":"profile-picker",children:[!o&&s("div",{className:"fbm-search",children:[a("span",{className:"fbm-search-icon",children:a(y,{name:"magnifying-glass"})}),a("input",{type:"search",className:"form-control rounded-pill",value:t,placeholder:i("profile_search"),"aria-label":i("profile_search"),autoComplete:"off",onChange:e=>u(e.target.value)})]}),a("div",{className:"fbm-choices fbm-profile-choices",role:"radiogroup","aria-label":i("wizard_profile"),children:n.map(e=>s("label",{className:`fbm-choice${e.id===r?" is-on":""}`,children:[a("input",{type:"radio",name:"fbm-profile",value:e.id,checked:e.id===r,disabled:o,onChange:()=>d(e)}),a("span",{className:"fbm-choice-dot","aria-hidden":"true"}),s("span",{className:"fbm-choice-text",children:[a("span",{className:"fbm-choice-title",children:e.name}),a("span",{className:"fbm-choice-hint",children:e.dimensions.length>0?e.dimensions.map(b=>b.label).join(" \xB7 "):i("profile_no_filters")})]})]},e.id))}),n.length===0&&a("p",{className:"text-muted fbm-hint",children:i("profile_none")}),!o&&n.length>=f&&a("p",{className:"text-muted fbm-hint",children:i("profile_more",{n:f})})]})};var C=_;export{C as default};
