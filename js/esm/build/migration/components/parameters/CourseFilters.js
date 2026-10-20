import p from"../ui/Icon";import{jsx as a,jsxs as l}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The courses a migration goes between: what the courses of each side have to have, one field for each filter of the profile.
 *
 * @module     local_filebank/migration/components/parameters/CourseFilters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const n=({dimensions:f,parameters:o,onChange:b,text:t,missing:c})=>{const i=(e,r,s)=>{const m={...o[e]};s===""?delete m[r]:m[r]=s,b({...o,[e]:m})};return f.length===0?a("p",{className:"fbm-muted",children:t("wizard_no_filters")}):l("div",{className:`fbm-sides${c?" has-problem":""}`,children:[["source","target"].map(e=>l("fieldset",{className:`fbm-side fbm-side-${e}`,children:[l("legend",{className:"fbm-side-title",children:[a(p,{name:e==="source"?"arrow-right-from-bracket":"arrow-right-to-bracket",gap:!0}),t(e==="source"?"wizard_from":"wizard_to")]}),f.map(r=>l("div",{className:"fbm-field",children:[a("label",{className:"fbm-label",htmlFor:`fbm-${e}-${r.key}`,children:r.label}),a("input",{id:`fbm-${e}-${r.key}`,type:"text",className:"fbm-input",maxLength:255,value:o[e][r.key]??"",placeholder:t("wizard_any"),onChange:s=>i(e,r.key,s.target.value)})]},r.key))]},e)),c&&a("p",{className:"fbm-problem",children:t("wizard_courses_required")})]})};var g=n;export{g as default};
