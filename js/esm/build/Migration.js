import{useEffect as d,useMemo as M,useState as n}from"react";import b from"./migration/components/list/MigrationList";import v from"./migration/components/review/Review";import w from"./migration/components/wizard/NewMigrationModal";import{useRoute as x}from"./migration/hooks/useRoute";import{describeError as O}from"./migration/lib/errors";import{createText as P}from"./migration/lib/text";import{getOptions as k}from"./migration/services/api";import y from"./migration/components/ui/Alert";import{jsx as r,jsxs as N}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the window that starts one and the page of each one, all in one application that
 * never leaves the page. The server works everything out; this only asks and shows.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_filebank/Migration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function E(o){const i=M(()=>P(o.strings),[o.strings]),e=x(o.planid,o.pageurl),[s,p]=n(null),[l,f]=n(""),[u,a]=n(!1),[c,g]=n(null);d(()=>{document.querySelectorAll(".fbm-boot").forEach(t=>t.remove()),k().then(p).catch(t=>f(O(t)))},[]);const m=t=>{g(t),a(!0)};return N("div",{className:"filebank-migration","data-region":"fbm-app",children:[l!==""&&r(y,{tone:"danger",live:!0,children:l}),e.planid>0?r(v,{id:e.planid,text:i,options:s,onBack:()=>e.open(0),onNew:m},e.planid):r(b,{text:i,pagesizes:o.pagesizes,onOpen:e.open,onNew:()=>m(null)}),r(w,{open:u,options:s,initial:c,text:i,onClose:()=>a(!1),onCreated:t=>{a(!1),e.open(t)}})]})}export{E as default};
