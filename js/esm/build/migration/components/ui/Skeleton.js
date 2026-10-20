import{jsx as e}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Grey blocks that hold the place of what is being read, so the page does not jump when it arrives.
 *
 * @module     local_filebank/migration/components/ui/Skeleton
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const a=({rows:l=4,label:o})=>e("div",{className:"fbm-skeleton",role:"status","aria-label":o,children:Array.from({length:l},(r,s)=>e("span",{className:"fbm-skeleton-row"},s))});var t=a;export{t as default};
