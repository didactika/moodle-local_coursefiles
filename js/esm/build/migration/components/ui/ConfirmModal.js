import{useEffect as M,useRef as b,useState as B}from"react";import{createPortal as S}from"react-dom";import{requireAsync as g}from"@moodle/lms/core/amd";import{jsx as R}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question before something that matters, in the save and cancel modal of Moodle: its header, its buttons and its look are the
 * ones of the theme, and only its body is drawn here. Cancel, its close button and Escape answer no.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=t=>t.default??t,P=({open:t,title:h,children:T,confirmLabel:E,cancelLabel:C,onConfirm:i,onCancel:s,tone:c="primary",busy:u=!1})=>{const[n,f]=B(null),o=b(null),d=b({onConfirm:i,onCancel:s});return d.current={onConfirm:i,onCancel:s},M(()=>{if(!t)return;let r=!0,a=null;return(async()=>{const[w,m]=await Promise.all([g("core/modal_save_cancel").then(p),g("core/modal_events").then(p)]),e=await w.create({title:h,body:"",removeOnClose:!0});if(!r){e.destroy();return}a=e,o.current=e,e.setSaveButtonText(E);const l=e.getRoot(),v=l[0].querySelector('[data-action="save"]');v&&c!=="primary"&&v.classList.replace("btn-primary",`btn-${c}`);const y=l[0].querySelector('[data-action="cancel"]');y&&(y.textContent=C),l.on(m.save,L=>{L.preventDefault(),d.current.onConfirm()}),l.on(m.hidden,()=>{o.current===e&&(o.current=null,d.current.onCancel())}),e.show(),f(e.getBody()[0])})(),()=>{r=!1,f(null),a&&o.current===a&&(o.current=null,a.hide())}},[t]),M(()=>{n?.closest(".modal-content")?.querySelectorAll(".modal-footer button, .modal-header button").forEach(r=>{r.disabled=u})},[u,n]),n?S(R("div",{className:"filebank-ui fbm-confirm-body",children:T}),n):null};var F=P;export{F as default};
