import c from"../ui/Icon";import{jsx as s,jsxs as m}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The steps of the page that starts a migration, listed down the left side: the ones that are done, the one that is open and the
 * ones that are still to come. A step that was reached can be opened again; one that was not cannot be skipped to.
 *
 * @module     local_filebank/migration/components/wizard/Stepper
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i=({steps:n,current:p,reached:r,onGo:l,text:a})=>s("nav",{className:"fbm-stepper","aria-label":a("wizard_steps"),children:s("ol",{className:"fbm-stepper-list",children:n.map((t,e)=>{const o=e===p?"is-current":e<p||e<=r?"is-done":"is-todo",b=e<=r;return s("li",{className:`fbm-stepper-item ${o}`,children:m("button",{type:"button",className:"fbm-stepper-button",disabled:!b,"aria-current":e===p?"step":void 0,onClick:()=>l(e),"data-step":t,children:[s("span",{className:"fbm-stepper-dot",children:o==="is-done"?s(c,{name:"check"}):e+1}),m("span",{className:"fbm-stepper-text",children:[s("span",{className:"fbm-stepper-title",children:a("step_"+t)}),s("span",{className:"fbm-stepper-hint",children:a("step_"+t+"_short")})]})]})},t)})})});var u=i;export{u as default};
