import c from"../ui/ChoiceCards";import{jsx as i,jsxs as d}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. Each choice is a card that says what it does; none of them stops a migration. What
 * happens with a file that is already different in the destination is decided in the last step, where it is known how many there
 * are.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const r=[{key:"missingtarget",values:["create","skip"]},{key:"duplicatepolicy",values:["skip-and-report","keep"]}],y=({rules:a,onChange:n,text:l})=>i("div",{className:"fbm-policies",children:r.map(e=>{const o=e.values.includes(a[e.key])?a[e.key]:e.values[0],t=s=>"policy_"+e.key+"_"+s.split("-").join("_");return d("fieldset",{className:"fbm-policy",children:[i("legend",{className:"fbm-policy-title",children:l("policy_"+e.key)}),i(c,{label:l("policy_"+e.key),name:`fbm-policy-${e.key}`,value:o,onChange:s=>n({...a,[e.key]:s}),choices:e.values.map(s=>({value:s,label:l(t(s)),hint:l(t(s)+"_hint")}))})]},e.key)})});var k=y;export{k as default};
