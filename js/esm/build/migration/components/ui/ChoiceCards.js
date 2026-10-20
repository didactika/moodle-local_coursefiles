import{jsx as e,jsxs as l}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A choice between a few things that need to be explained: each one is a card with its name and what it does, and the chosen one is
 * marked. It is a group of real radio buttons, so the keyboard and assistive technology work as they do in a form.
 *
 * @module     local_filebank/migration/components/ui/ChoiceCards
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=({label:n,name:i,value:s,choices:r,onChange:t})=>e("div",{className:"fbm-choices",role:"radiogroup","aria-label":n,children:r.map(a=>l("label",{className:`fbm-choice${a.value===s?" is-on":""}`,children:[e("input",{type:"radio",name:i,value:a.value,checked:a.value===s,onChange:()=>t(a.value)}),e("span",{className:"fbm-choice-dot","aria-hidden":"true"}),l("span",{className:"fbm-choice-text",children:[e("span",{className:"fbm-choice-title",children:a.label}),a.hint&&e("span",{className:"fbm-choice-hint",children:a.hint})]})]},a.value))});var c=o;export{c as default};
