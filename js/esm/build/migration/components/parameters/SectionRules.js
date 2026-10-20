import{MAX_SECTION_PAIRS as g}from"../../lib/parameters";import _ from"../ui/Chips";import m from"../ui/Icon";import{jsx as a,jsxs as r}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * How the sections of a source course are matched with the sections of the destination course: by position, by name, or pair by
 * pair.
 *
 * @module     local_filebank/migration/components/parameters/SectionRules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const d=["ordinal","name","mapping"],f=({rules:s,onChange:c,text:t,missing:p})=>{const o=s.sectionmap,u=(e,n,i)=>c({...s,sectionmap:o.map((l,b)=>b===e?{...l,[n]:i}:l)});return r("div",{className:"fbm-sections",children:[a(_,{label:t("rules_sections"),value:s.sectionstrategy,onChange:e=>c({...s,sectionstrategy:e,sectionmap:e==="mapping"&&o.length===0?[{source:"",target:""}]:o}),chips:d.map(e=>({value:e,label:t("rules_sections_"+e)}))}),a("p",{className:"fbm-muted fbm-hint",children:t("rules_sections_"+s.sectionstrategy+"_hint")}),s.sectionstrategy==="mapping"&&r("div",{className:"fbm-pairs",children:[o.map((e,n)=>r("div",{className:"fbm-pair",children:[a("input",{type:"text",inputMode:"numeric",className:`fbm-input${p&&!/^[1-9][0-9]*$/.test(e.source)?" is-invalid":""}`,value:e.source,"aria-label":t("rules_section_source"),placeholder:t("rules_section_source"),onChange:i=>u(n,"source",i.target.value)}),a(m,{name:"arrow-right"}),a("input",{type:"text",inputMode:"numeric",className:`fbm-input${p&&!/^[1-9][0-9]*$/.test(e.target)?" is-invalid":""}`,value:e.target,"aria-label":t("rules_section_target"),placeholder:t("rules_section_target"),onChange:i=>u(n,"target",i.target.value)}),r("button",{type:"button",className:"fbm-iconbtn",title:t("rules_remove"),onClick:()=>c({...s,sectionmap:o.filter((i,l)=>l!==n)}),children:[a(m,{name:"trash"}),a("span",{className:"fbm-sr",children:t("rules_remove")})]})]},n)),r("button",{type:"button",className:"fbm-btn",disabled:o.length>=g,onClick:()=>c({...s,sectionmap:[...o,{source:"",target:""}]}),children:[a(m,{name:"plus",gap:!0}),t("rules_section_add")]})]})]})};var y=f;export{y as default};
