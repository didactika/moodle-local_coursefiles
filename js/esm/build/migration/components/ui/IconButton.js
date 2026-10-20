import i from"./Icon";import{jsx as t,jsxs as c}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A button that is only an icon, tinted with the colour of what it does. The label is read by assistive technology and is the title
 * of the button, so a row of them does not need a word on each.
 *
 * @module     local_filebank/migration/components/ui/IconButton
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=({icon:e,label:n,onClick:l,tone:o="neutral",disabled:a=!1})=>c("button",{type:"button",className:`btn btn-outline-secondary fbm-iconpill${o==="neutral"?"":` tone-${o}`}`,title:n,disabled:a,onClick:l,children:[t(i,{name:e}),t("span",{className:"filebank-visually-hidden",children:n})]});var b=s;export{b as default};
