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
 */const p=async(r,t)=>{const e=r.closest("[data-filebank-list]");if(!e){window.location.assign(t);return}const d=e.querySelector(".table-responsive"),f=d?d.offsetHeight:0;e.setAttribute("aria-busy","true");try{const c=await fetch(t,{credentials:"same-origin"}),o=new DOMParser().parseFromString(await c.text(),"text/html").querySelector(`[data-filebank-list="${e.dataset.filebankList}"]`);if(!c.ok||!o){window.location.assign(t);return}const i=e.querySelector(".filebank-pagination"),s=o.querySelector(".filebank-pagination");if(i&&s){const n=i.querySelector("select"),l=s.querySelector("select");n&&l&&(n.innerHTML=l.innerHTML,n.value=l.value,n.dataset.url=l.dataset.url||"");const u=i.querySelector(".fbm-listbar-pages"),b=s.querySelector(".fbm-listbar-pages");u&&b&&u.replaceWith(document.importNode(b,!0)),s.remove(),Array.from(e.childNodes).filter(a=>a!==i).forEach(a=>a.remove()),Array.from(o.childNodes).forEach(a=>e.insertBefore(document.importNode(a,!0),i))}else e.replaceChildren(...Array.from(o.childNodes,n=>document.importNode(n,!0)));const m=e.querySelector(".table-responsive");m&&f>0&&(m.style.minHeight=`${f}px`),window.history.replaceState(window.history.state,"",t),e.dispatchEvent(new CustomEvent("filebank:listchanged",{bubbles:!0}))}catch{window.location.assign(t)}finally{e.removeAttribute("aria-busy")}},h=()=>{document.body.dataset.filebankLists||(document.body.dataset.filebankLists="true",document.addEventListener("click",r=>{const t=r.target instanceof Element?r.target.closest("[data-filebank-list] .filebank-pagination a[href]"):null;t&&(r.preventDefault(),p(t,t.href))}))};export{h as registerLists,p as swapList};
