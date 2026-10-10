import a from"@moodle/lms/local_coursefiles/local/selectors";/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_coursefiles/local/editor/actions
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const m=(t,l)=>{const n=new Set([...t.getBody().querySelectorAll(a.links)].map(e=>e.href));if(l.files.some(e=>n.has(e.url))){const e=document.createElement("ul");return l.files.filter(o=>!n.has(o.url)).forEach(o=>{const s=document.createElement("li"),r=document.createElement("a");r.href=o.url,r.textContent=o.name,s.append(r),e.append(s)}),e.children.length&&t.undoManager.transact(()=>{t.selection.collapse(!1),t.insertContent(e.outerHTML),t.save()}),!0}return!1};export{m as appendLinks};
