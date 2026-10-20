import{useRef as n,useState as s}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Keep the height of a list when it goes to another page, so a last page with fewer rows does not make the page jump; a new
 * search or filter lets it take its own height again.
 *
 * @module     local_filebank/migration/hooks/useKeptHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=()=>{const e=n(null),[t,i]=s(0);return{ref:e,style:t>0?{minHeight:`${t}px`}:void 0,keep:()=>i(r=>Math.max(r,e.current?.offsetHeight??0)),release:()=>i(0)}};export{o as useKeptHeight};
