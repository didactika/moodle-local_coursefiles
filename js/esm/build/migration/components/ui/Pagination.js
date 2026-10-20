import{useId as h}from"react";import{pageSizeChoices as N}from"../../lib/paging";import P from"./Icon";import{Fragment as y,jsx as e,jsxs as t}from"react/jsx-runtime";/**
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
 */const _=({page:o,perpage:n,total:i,onPage:d,text:s,onPerPage:r})=>{const m=h(),b=Math.max(1,Math.ceil(i/Math.max(1,n))),l=r!==void 0&&i>0?{sizes:N(i),onPerPage:r}:null;if(b<2&&l===null)return null;const f=i>0?o*n+1:0,p=Math.min((o+1)*n,i),c=(a,v,g,u)=>e("button",{type:"button",className:"btn btn-link btn-icon",title:u,"aria-label":u,disabled:!v,onClick:()=>d(a),children:e(P,{name:g})});return t("nav",{"aria-label":s("pagination_label"),className:"fbm-listbar fbm-pagination",children:[e("div",{className:"fbm-listbar-size",children:l!==null&&t(y,{children:[e("label",{className:"fbm-listbar-label",htmlFor:m,children:s("per_page")}),t("select",{id:m,className:"form-select form-select-sm rounded-pill",value:n,onChange:a=>l.onPerPage(Number(a.target.value)),children:[!l.sizes.includes(n)&&e("option",{value:n,children:n}),l.sizes.map(a=>e("option",{value:a,children:a===i?s("all"):a},a))]})]})}),t("div",{className:"fbm-listbar-pages",children:[e("span",{className:"text-muted small","aria-live":"polite",children:s("pagination_range",{from:f,to:p,total:i})}),t("div",{className:"fbm-actions",children:[c(o-1,o>0,"arrow-left",s("pagination_previous")),c(o+1,o<b-1,"arrow-right",s("pagination_next"))]})]})]})};var z=_;export{z as default};
