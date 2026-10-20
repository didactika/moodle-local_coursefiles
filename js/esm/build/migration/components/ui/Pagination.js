import{useEffect as P,useId as N}from"react";import{pageSizeChoices as z}from"../../lib/paging";import _ from"./Icon";import{Fragment as M,jsx as s,jsxs as t}from"react/jsx-runtime";/**
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
 */const y=({page:o,perpage:e,total:n,onPage:d,text:l,onPerPage:m})=>{const b=N(),c=Math.max(1,Math.ceil(n/Math.max(1,e))),a=m!==void 0&&n>0?{sizes:z(n),onPerPage:m}:null,r=a===null||a.sizes.includes(e)?e:e>=n&&a.sizes.includes(n)?n:a.sizes[0];if(P(()=>{a!==null&&r!==e&&a.onPerPage(r)},[r,e]),c<2&&a===null)return null;const v=n>0?o*e+1:0,p=Math.min((o+1)*e,n),u=(i,g,h,f)=>s("button",{type:"button",className:"btn btn-link btn-icon fbm-pager-arrow",title:f,"aria-label":f,disabled:!g,onClick:()=>d(i),children:s(_,{name:h})});return t("nav",{"aria-label":l("pagination_label"),className:"fbm-listbar fbm-pagination",children:[s("div",{className:"fbm-listbar-size",children:a!==null&&t(M,{children:[s("label",{className:"fbm-listbar-label",htmlFor:b,children:l("per_page")}),s("select",{id:b,className:"form-select form-select-sm rounded-pill",value:r,onChange:i=>a.onPerPage(Number(i.target.value)),children:a.sizes.map(i=>s("option",{value:i,children:i===n?l("all"):i},i))})]})}),t("div",{className:"fbm-listbar-pages",children:[s("span",{className:"text-muted small","aria-live":"polite",children:l("pagination_range",{from:v,to:p,total:n})}),t("div",{className:"fbm-actions",children:[u(o-1,o>0,"chevron-left",l("pagination_previous")),u(o+1,o<c-1,"chevron-right",l("pagination_next"))]})]})]})};var x=y;export{x as default};
