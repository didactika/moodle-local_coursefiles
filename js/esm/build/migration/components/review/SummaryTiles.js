import i from"../ui/StatTile";import{jsx as a,jsxs as c}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The figures of a migration at the top of its page: what would be copied, overwritten or is already there, what waits for a
 * decision and how many folders it covers. Each one leads to the part of the page that lists what it counts.
 *
 * @module     local_filebank/migration/components/review/SummaryTiles
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=({summary:o,text:e,onFiles:r,onTab:n,active:t})=>{const l=t.tab==="files";return c("div",{className:"fbm-tiles","data-region":"summary",children:[a(i,{label:e("summary_tocopy"),value:o.tocopy,tone:"success",icon:"copy",hint:e("summary_tocopy_hint"),active:l&&t.bucket==="copy",onClick:()=>r("copy")}),a(i,{label:e("summary_tooverwrite"),value:o.tooverwrite,tone:"warning",icon:"pen-to-square",hint:e("summary_tooverwrite_hint"),active:!1,onClick:()=>r("copy")}),a(i,{label:e("summary_existing"),value:o.existing,tone:"neutral",icon:"equals",hint:e("summary_existing_hint"),active:l&&t.bucket==="existing",onClick:()=>r("existing")}),a(i,{label:e("summary_left"),value:o.skipped,tone:"neutral",icon:"hand",hint:e("summary_left_hint"),active:l&&t.bucket==="left",onClick:()=>r("left")}),a(i,{label:e("summary_folders"),value:o.folders,tone:"primary",icon:"folder-tree",hint:e("summary_folders_hint",{from:o.sourcecourses,to:o.targetcourses}),active:t.tab==="folders",onClick:()=>n("folders")})]})};var m=s;export{m as default};
