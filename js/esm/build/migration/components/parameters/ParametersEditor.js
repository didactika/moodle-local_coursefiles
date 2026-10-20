import{parametersFor as p,parametersOf as P}from"../../lib/parameters";import{getMigration as a}from"../../services/api";import y from"./CorrespondenceRows";import g from"./CourseFilters";import v from"./PolicyFields";import h from"./ProfilePicker";import k from"./SectionRules";import{jsx as n}from"react/jsx-runtime";/**
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
 */const S=({step:l,options:t,parameters:e,onChange:o,onOptions:c,text:i,lockProfile:f=!1,problems:u})=>{const d=t.profiles.find(r=>r.id===e.profileid);if(l==="profile")return n(h,{profiles:t.profiles,chosen:e.profileid,locked:f,invalid:u.includes("profile"),onPick:r=>{o(p(r)),c("",r.id)},onSearch:r=>c(r,e.profileid),text:i});if(!d)return null;if(l==="courses")return n(g,{dimensions:d.dimensions,parameters:e,onChange:o,text:i,missing:u.includes("courses")});if(l==="folders"){const r=async s=>{const m=P(await a(s));o({...e,rules:{...e.rules,correspondences:m.rules.correspondences}})};return n(y,{rows:e.rules.correspondences,previous:t.previous,onReuse:s=>void r(s).catch(()=>{}),modules:t.modules,onChange:s=>o({...e,rules:{...e.rules,correspondences:s}}),text:i,missing:u.includes("correspondence")})}return l==="sections"?n(k,{rules:e.rules,onChange:r=>o({...e,rules:r}),text:i,missing:u.includes("sections")}):n(v,{rules:e.rules,onChange:r=>o({...e,rules:r}),text:i})};var C=S;export{C as default};
