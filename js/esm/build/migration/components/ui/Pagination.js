import h from"./Icon";import{Fragment as P,jsx as n,jsxs as t}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar under a list: how many rows a page holds, the rows that are shown out of all, and the arrows to the previous and next page.
 * It is always at the bottom of the list, where the eye ends.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const N=({page:e,perpage:s,total:o,onPage:p,text:i,sizes:r,onPerPage:m})=>{const b=Math.max(1,Math.ceil(o/Math.max(1,s))),l=r!==void 0&&m!==void 0&&o>0?{sizes:r,onPerPage:m}:null;if(b<2&&l===null)return null;const d=o>0?e*s+1:0,f=Math.min((e+1)*s,o),c=(a,v,g,u)=>n("button",{type:"button",className:"btn btn-link btn-icon",title:u,"aria-label":u,disabled:!v,onClick:()=>p(a),children:n(h,{name:g})});return t("nav",{"aria-label":i("pagination_label"),className:"fbm-listbar fbm-pagination",children:[n("div",{className:"fbm-listbar-size",children:l!==null&&t(P,{children:[n("span",{className:"fbm-listbar-label",children:i("per_page")}),n("select",{className:"form-select form-select-sm rounded-pill","aria-label":i("per_page"),value:s,onChange:a=>l.onPerPage(Number(a.target.value)),children:l.sizes.map(a=>n("option",{value:a,children:a},a))})]})}),t("div",{className:"fbm-listbar-pages",children:[n("span",{className:"text-muted small","aria-live":"polite",children:i("pagination_range",{from:d,to:f,total:o})}),t("div",{className:"fbm-actions",children:[c(e-1,e>0,"chevron-left",i("pagination_previous")),c(e+1,e<b-1,"chevron-right",i("pagination_next"))]})]})]})};var y=N;export{y as default};
