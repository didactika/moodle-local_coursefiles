import{useEffect as l,useMemo as f,useState as p}from"react";import g from"./migration/components/list/MigrationList";import c from"./migration/components/review/Review";import u from"./migration/components/ui/Alert";import w from"./migration/components/wizard/MigrationWizard";import{useRoute as v}from"./migration/hooks/useRoute";import{describeError as E}from"./migration/lib/errors";import{createText as M}from"./migration/lib/text";import{getOptions as b}from"./migration/services/api";import{jsx as i,jsxs as x}from"react/jsx-runtime";/**
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
 */function k(o){const n=f(()=>M(o.strings),[o.strings]),e=v(o.planid,o.pageurl),[a,s]=p(null),[m,d]=p("");l(()=>{document.querySelectorAll(".fbm-boot").forEach(r=>r.remove()),b().then(s).catch(r=>d(E(r)))},[]);let t;return e.mode==="new"||e.mode==="edit"?t=i(w,{mode:e.mode,options:a,migrationId:e.mode==="edit"?e.planid:e.from,text:n,onClose:()=>e.mode==="edit"?e.openReview(e.planid):e.openList(),onDone:e.openReview},`${e.mode}-${e.planid}-${e.from}`):e.mode==="review"?t=i(c,{id:e.planid,text:n,onBack:e.openList,onEdit:()=>e.openEdit(e.planid),onNew:()=>e.openNew(e.planid)},e.planid):t=i(g,{text:n,pagesizes:o.pagesizes,onOpen:e.openReview,onNew:()=>e.openNew()}),x("div",{className:"filebank-migration","data-region":"fbm-app",children:[m!==""&&i(u,{tone:"danger",live:!0,children:m}),t]})}export{k as default};
