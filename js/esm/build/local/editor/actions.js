import c from"@moodle/lms/local_multipicker/local/selectors";/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_multipicker/local/editor/actions
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=(t,l)=>{const n=new Set([...t.getBody().querySelectorAll(c.links)].map(e=>e.href));if(l.files.some(e=>n.has(e.url))){const e=document.createElement("ul");return l.files.filter(o=>!n.has(o.url)).forEach(o=>{const a=document.createElement("li"),r=document.createElement("a");r.href=o.url,r.textContent=o.name,a.append(r),e.append(a)}),e.children.length&&t.undoManager.transact(()=>{t.selection.collapse(!1),t.insertContent(e.outerHTML),t.save()}),!0}return!1};export{s as appendLinks};
