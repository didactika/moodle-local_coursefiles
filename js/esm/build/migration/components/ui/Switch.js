import{jsx as t}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A switch that turns something on or off, which a person tells from a check box because it takes effect at once.
 *
 * @module     local_filebank/migration/components/ui/Switch
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const l=({checked:e,partial:o=!1,label:a,onChange:i,disabled:s=!1})=>t("button",{type:"button",role:"switch","aria-checked":o?"mixed":e,"aria-label":a,title:a,className:`fbm-switch${e?" is-on":""}${o?" is-partial":""}`,disabled:s,onClick:()=>i(o?!0:!e),children:t("span",{className:"fbm-switch-knob"})});var n=l;export{n as default};
