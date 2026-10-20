/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Lists of server pages that change page in place: the arrows and the size of a page fetch the next page and replace only the
 * region of the list (its table and its pager), keeping its height so a shorter last page does not make the page jump.
 *
 * @module local_filebank/local/admin/lists
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=async(i,e)=>{const t=i.closest("[data-filebank-list]");if(!t){window.location.assign(e);return}const s=t.querySelector(".table-responsive"),a=s?s.offsetHeight:0;t.setAttribute("aria-busy","true");try{const n=await fetch(e,{credentials:"same-origin"}),o=new DOMParser().parseFromString(await n.text(),"text/html").querySelector(`[data-filebank-list="${t.dataset.filebankList}"]`);if(!n.ok||!o){window.location.assign(e);return}t.replaceChildren(...Array.from(o.childNodes,l=>document.importNode(l,!0)));const r=t.querySelector(".table-responsive");r&&a>0&&(r.style.minHeight=`${a}px`),window.history.replaceState(window.history.state,"",e),t.dispatchEvent(new CustomEvent("filebank:listchanged",{bubbles:!0}))}catch{window.location.assign(e)}finally{t.removeAttribute("aria-busy")}},f=()=>{document.body.dataset.filebankLists||(document.body.dataset.filebankLists="true",document.addEventListener("click",i=>{const e=i.target instanceof Element?i.target.closest("[data-filebank-list] .filebank-pagination a[href]"):null;e&&(i.preventDefault(),c(e,e.href))}))};export{f as registerLists,c as swapList};
