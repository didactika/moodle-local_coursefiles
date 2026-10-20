import{jsx as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A bar that says how much of something is done.
 *
 * @module     local_filebank/migration/components/ui/Progress
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i=({value:e,max:r,label:s,tone:o="primary"})=>{const t=r>0?Math.min(100,Math.round(e/r*100)):0;return a("div",{className:`fbm-progress tone-${o}`,role:"progressbar","aria-label":s,"aria-valuemin":0,"aria-valuemax":r,"aria-valuenow":e,children:a("div",{className:"fbm-progress-fill",style:{width:`${t}%`}})})};var l=i;export{l as default};
