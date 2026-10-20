import{useRef as i,useState as o}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Keep the height of a list when it goes to another page, so the bar under it stays where it was and a last page with fewer rows
 * does not make the page jump; a new search or filter lets the list take its own height again.
 *
 * @module     local_filebank/migration/hooks/useKeptHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const u=()=>{const e=i(null),[t,n]=o(0),r=t>0?{minHeight:`${t}px`}:void 0;return{ref:e,style:r,keep:()=>n(s=>Math.max(s,e.current?.offsetHeight??0)),release:()=>n(0)}};export{u as useKeptHeight};
