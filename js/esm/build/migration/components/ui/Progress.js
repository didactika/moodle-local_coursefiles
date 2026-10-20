import{jsx as s}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A bar that says how much of something is done.
 *
 * @module     local_filebank/migration/components/ui/Progress
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const t=({value:e,max:r,label:o,tone:a="primary"})=>{const n=r>0?Math.min(100,Math.round(e/r*100)):0;return s("div",{className:"progress",role:"progressbar","aria-label":o,"aria-valuemin":0,"aria-valuemax":r,"aria-valuenow":e,children:s("div",{className:`progress-bar bg-${a==="neutral"?"secondary":a}`,style:{width:`${n}%`}})})};var i=t;export{i as default};
