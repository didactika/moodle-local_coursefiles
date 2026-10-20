import{useCallback as w,useEffect as v,useMemo as b,useState as a}from"react";import k from"./migration/components/list/MigrationList";import O from"./migration/components/review/Review";import E from"./migration/components/ui/Alert";import M from"./migration/components/wizard/MigrationWizard";import{useRoute as h}from"./migration/hooks/useRoute";import{describeError as d}from"./migration/lib/errors";import{createText as x}from"./migration/lib/text";import{getOptions as l}from"./migration/services/api";import{jsx as n,jsxs as N}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the page that starts one or changes its parameters, and the page of each one, all
 * in one application that never leaves the page. The server works everything out; this only asks and shows.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_filebank/Migration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function y(o){const r=b(()=>x(o.strings),[o.strings]),e=h(o.planid,o.pageurl),[f,m]=a(null),[p,s]=a("");v(()=>{document.querySelectorAll(".fbm-boot").forEach(t=>t.remove()),l().then(m).catch(t=>s(d(t)))},[]);const c=w((t,g)=>{l(t,g).then(m).catch(u=>s(d(u)))},[]);let i;return e.mode==="new"||e.mode==="edit"?i=n(M,{mode:e.mode,options:f,migrationId:e.mode==="edit"?e.planid:e.from,text:r,onClose:()=>e.mode==="edit"?e.openReview(e.planid):e.openList(),onDone:e.openReview,onOptions:c},`${e.mode}-${e.planid}-${e.from}`):e.mode==="review"?i=n(O,{id:e.planid,text:r,onBack:e.openList,onEdit:()=>e.openEdit(e.planid),onNew:()=>e.openNew(e.planid)},e.planid):i=n(k,{text:r,pagesizes:o.pagesizes,onOpen:e.openReview,onNew:()=>e.openNew()}),N("div",{className:"filebank-migration","data-region":"fbm-app",children:[p!==""&&n(E,{tone:"danger",live:!0,children:p}),i]})}export{y as default};
