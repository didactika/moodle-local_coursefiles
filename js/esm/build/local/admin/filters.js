/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Filters that apply as they are set: a list, a date or a choice sends the form at once and what is typed does it a
 * moment after the last key, so there is no button to press. The page comes back with the cursor where it was, and a
 * drop-down of filters closes when the person clicks outside it or presses Escape.
 *
 * @module local_filebank/local/admin/filters
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s="local_filebank/live-focus",c=()=>{try{const t=window.sessionStorage.getItem(s)??"";return window.sessionStorage.removeItem(s),t}catch{return""}},r=()=>{const t=c();document.querySelectorAll("form[data-filebank-live-filters]").forEach(e=>{if(e.dataset.ready)return;e.dataset.ready="true";let a=0;const o=()=>{window.clearTimeout(a);const n=document.activeElement;if(n instanceof HTMLInputElement&&e.contains(n)&&n.name!=="")try{window.sessionStorage.setItem(s,n.name)}catch{}e.requestSubmit()};e.addEventListener("change",o),e.addEventListener("input",n=>{n.target instanceof HTMLElement&&n.target.matches("[data-filebank-live-text]")&&(window.clearTimeout(a),a=window.setTimeout(o,400))});const i=t!==""?e.querySelector(`input[name="${CSS.escape(t)}"]`):null;i&&(i.focus(),i.setSelectionRange(i.value.length,i.value.length))})},l=()=>{if(document.body.dataset.filebankMenus)return;document.body.dataset.filebankMenus="true";const t=e=>{document.querySelectorAll("details.fbm-filterbox[open]").forEach(a=>{(!(e instanceof Node)||!a.contains(e))&&(a.open=!1)})};document.addEventListener("click",e=>{e.target instanceof Element&&e.target.closest(".daterangepicker")||t(e.target)}),document.addEventListener("keydown",e=>{e.key==="Escape"&&t(null)})};export{l as registerFilterMenus,r as registerLiveFilters};
