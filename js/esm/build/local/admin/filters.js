import{swapList as m}from"@moodle/lms/local_filebank/local/admin/lists";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Filters that apply as they are set: a list, a date or a choice applies at once and what is typed does it a moment after the
 * last key, so there is no button to press. A form that names its list replaces only that list, so the page does not move and
 * the cursor stays where it is; any other form sends the page, which comes back with the cursor where it was. A drop-down of
 * filters closes when the person clicks outside it or presses Escape.
 *
 * @module local_filebank/local/admin/filters
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const f=400,o="local_filebank/live-focus",g=()=>{try{const t=window.sessionStorage.getItem(o)??"";return window.sessionStorage.removeItem(o),t}catch{return""}},E=()=>{const t=g();document.querySelectorAll("form[data-filebank-live-filters]").forEach(e=>{if(e.dataset.ready)return;e.dataset.ready="true";let n=0;const r=()=>{window.clearTimeout(n);const i=e.dataset.filebankTarget?document.querySelector(`[data-filebank-list="${e.dataset.filebankTarget}"]`):null;if(i){const c=new URL(e.getAttribute("action")||"",window.location.href),l=new URLSearchParams;new FormData(e).forEach((d,u)=>{typeof d=="string"&&l.append(u,d)}),c.search=l.toString(),m(i,c.toString());return}const s=document.activeElement;if(s instanceof HTMLInputElement&&e.contains(s)&&s.name!=="")try{window.sessionStorage.setItem(o,s.name)}catch{}e.requestSubmit()};e.addEventListener("change",r),e.addEventListener("input",i=>{i.target instanceof HTMLElement&&i.target.matches("[data-filebank-live-text]")&&(window.clearTimeout(n),n=window.setTimeout(r,f))});const a=t!==""?e.querySelector(`input[name="${CSS.escape(t)}"]`):null;a&&(a.focus(),a.setSelectionRange(a.value.length,a.value.length))})},p=()=>{if(document.body.dataset.filebankMenus)return;document.body.dataset.filebankMenus="true";const t=e=>{document.querySelectorAll("details.fbm-filterbox[open]").forEach(n=>{(!(e instanceof Node)||!n.contains(e))&&(n.open=!1)})};document.addEventListener("click",e=>{e.target instanceof Element&&e.target.closest(".daterangepicker")||t(e.target)}),document.addEventListener("keydown",e=>{e.key==="Escape"&&t(null)})};export{p as registerFilterMenus,E as registerLiveFilters};
