import{useEffect as E,useState as c}from"react";import{problemsOf as M}from"../../lib/parameters";import k from"../parameters/ParametersEditor";import P from"../ui/Alert";import l from"../ui/Icon";import C from"../ui/Modal";import{Fragment as u,jsx as e,jsxs as n}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The window that changes the parameters of a migration that has not run. The migration is worked out again where it is, so it
 * keeps its number, its date and what was already decided; no other migration is created.
 *
 * @module     local_filebank/migration/components/review/EditParametersModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const A=({open:m,options:t,initial:i,text:o,busy:r,error:p,onSave:g,onClose:d})=>{const[a,f]=c(i),[v,b]=c([]);E(()=>{m&&(f(i),b([]))},[m,i]);const h=t?.profiles.find(s=>s.id===a.profileid)?.dimensions.length??0,y=()=>{const s=M(a,h);b(s),s.length===0&&g(a)};return n(C,{open:m,size:"large",busy:r,title:n(u,{children:[e(l,{name:"sliders",gap:!0}),o("edit_title")]}),onClose:d,closeLabel:o("close"),footer:n(u,{children:[e("button",{type:"button",className:"fbm-btn",onClick:d,disabled:r,children:o("cancel")}),n("button",{type:"button",className:"fbm-btn fbm-btn-primary",onClick:y,disabled:r||!t,children:[r?e(l,{name:"spinner",gap:!0,spin:!0}):e(l,{name:"check",gap:!0}),o("edit_save")]})]}),children:[e(P,{tone:"info",children:o("edit_hint")}),p!==""&&e(P,{tone:"danger",live:!0,children:p}),t?e(k,{options:t,parameters:a,onChange:f,text:o,lockProfile:!0,problems:v}):e("p",{className:"fbm-muted",children:o("loading")})]})};var I=A;export{I as default};
