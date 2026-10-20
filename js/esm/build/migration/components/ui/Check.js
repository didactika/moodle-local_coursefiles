import{useEffect as r,useRef as i}from"react";import{jsx as f}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A check box that can also say that some of what it covers is checked.
 *
 * @module     local_filebank/migration/components/ui/Check
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=({checked:e,partial:t=!1,label:o,onChange:a,disabled:c=!1})=>{const n=i(null);return r(()=>{n.current&&(n.current.indeterminate=t&&!e)},[t,e]),f("input",{ref:n,type:"checkbox",className:"fbm-check",checked:e,disabled:c,"aria-label":o,title:o,onChange:l=>a(l.target.checked)})};var d=s;export{d as default};
