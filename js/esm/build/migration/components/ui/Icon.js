import{iconClass as s}from"../../lib/icons";import{jsx as i}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * An icon of Font Awesome, which Moodle already loads, so it takes the colour and the size of the text around it.
 *
 * @module     local_filebank/migration/components/ui/Icon
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const e=({name:a,gap:o=!1,spin:n=!1})=>i("i",{className:`${s(a)}${o?" fbm-gap":""}${n?" fa-spin":""}`,"aria-hidden":"true"});var f=e;export{f as default};
