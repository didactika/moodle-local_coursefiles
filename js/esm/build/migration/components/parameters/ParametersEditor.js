import{parametersFor as p,profileRules as m}from"../../lib/parameters";import a from"./CorrespondenceRows";import P from"./CourseFilters";import y from"./PolicyFields";import b from"./SectionRules";import{jsx as i,jsxs as v}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One step of what a person decides about a migration before it is worked out: the profile, the courses it goes between, which
 * folders go where, how sections match and what to do when something does not match. The same steps start a new migration and
 * change one that has not run.
 *
 * @module     local_filebank/migration/components/parameters/ParametersEditor
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const g=({step:l,options:n,parameters:o,onChange:s,text:r,lockProfile:u=!1,problems:t})=>{const d=n.profiles.find(e=>e.id===o.profileid);return l==="profile"?v("select",{className:`fbm-select fbm-select-wide${t.includes("profile")?" is-invalid":""}`,"aria-label":r("wizard_profile"),value:o.profileid,disabled:u,onChange:e=>{const f=n.profiles.find(c=>c.id===Number(e.target.value));f&&s(p(f))},children:[o.profileid<=0&&i("option",{value:0,children:r("wizard_profile_choose")}),n.profiles.map(e=>i("option",{value:e.id,children:e.name},e.id))]}):d?l==="courses"?i(P,{dimensions:d.dimensions,parameters:o,onChange:s,text:r,missing:t.includes("courses")}):l==="folders"?i(a,{rows:o.rules.correspondences,profileRows:m(d).correspondences,modules:n.modules,onChange:e=>s({...o,rules:{...o.rules,correspondences:e}}),text:r,missing:t.includes("correspondence")}):l==="sections"?i(b,{rules:o.rules,onChange:e=>s({...o,rules:e}),text:r,missing:t.includes("sections")}):i(y,{rules:o.rules,onChange:e=>s({...o,rules:e}),text:r}):null};var E=g;export{E as default};
