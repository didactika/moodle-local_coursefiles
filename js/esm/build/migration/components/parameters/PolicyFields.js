import n from"../ui/Chips";import{jsx as i,jsxs as u}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. None of the choices stops a migration: what is not decided now is decided afterwards,
 * file by file or all at once, inside the same migration.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=[{key:"missingtarget",values:["create","skip"]},{key:"conflictpolicy",values:["block","overwrite"]},{key:"duplicatepolicy",values:["skip-and-report","keep"]}],r=({rules:a,onChange:c,text:l})=>i("div",{className:"fbm-policies",children:p.map(e=>{const o=e.values.includes(a[e.key])?a[e.key]:e.values[0],t=s=>"policy_"+e.key+"_"+s.split("-").join("_");return u("div",{className:"fbm-policy",children:[i("div",{className:"fbm-label",children:l("policy_"+e.key)}),i(n,{label:l("policy_"+e.key),value:o,onChange:s=>c({...a,[e.key]:s}),chips:e.values.map(s=>({value:s,label:l(t(s))}))}),i("p",{className:"fbm-muted fbm-hint",children:l(t(o)+"_hint")})]},e.key)})});var y=r;export{y as default};
