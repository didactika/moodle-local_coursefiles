import{pageWindow as f}from"../../lib/paging";import s from"./Icon";import{jsx as a,jsxs as m}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar that goes from one page of a list to another, without leaving the page.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=({page:t,perpage:g,total:r,onPage:p,text:i})=>{const n=f(t,g,r);if(n.pages<2)return null;const o=(e,l,u,b)=>a("li",{children:a("button",{type:"button",className:"fbm-page",title:l,"aria-label":l,onClick:()=>p(e),children:u})},b);return m("nav",{"aria-label":i("pagination_label"),className:"fbm-pagination",children:[m("ul",{className:"fbm-pages",children:[n.first&&o(0,i("pagination_first"),a(s,{name:"angles-left"}),"first"),t>0&&o(t-1,i("pagination_previous"),a(s,{name:"angle-left"}),"previous"),n.startDots&&a("li",{children:a("span",{className:"fbm-page is-gap",children:"..."})},"start"),n.shown.map(e=>a("li",{children:a("button",{type:"button",className:`fbm-page${e===t?" is-current":""}`,onClick:()=>p(e),"aria-current":e===t?"page":void 0,children:e+1})},e)),n.endDots&&a("li",{children:a("span",{className:"fbm-page is-gap",children:"..."})},"end"),n.last&&o(n.pages-1,i("pagination_last"),n.pages,"last"),t<n.pages-1&&o(t+1,i("pagination_next"),a(s,{name:"angle-right"}),"next")]}),a("div",{className:"fbm-muted fbm-pagination-info",children:i("pagination_info",{current:t+1,total:n.pages,count:r})})]})};var d=c;export{d as default};
