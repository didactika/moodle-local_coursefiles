import s from"../ui/StatTile";import{jsx as n,jsxs as u}from"react/jsx-runtime";/**
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
 */const l=({summary:o,text:e,onFiles:t,onTab:c,active:i})=>{const a=o.conflicts+o.openissues,r=i.tab==="files";return u("div",{className:"fbm-tiles","data-region":"summary",children:[n(s,{label:e("summary_tocopy"),value:o.tocopy,tone:"success",icon:"copy",hint:e("summary_tocopy_hint"),active:r&&i.bucket==="copy",onClick:()=>t("copy")}),n(s,{label:e("summary_tooverwrite"),value:o.tooverwrite,tone:"warning",icon:"pen-to-square",hint:e("summary_tooverwrite_hint"),active:!1,onClick:()=>t("copy")}),n(s,{label:e("summary_existing"),value:o.existing,tone:"neutral",icon:"equals",hint:e("summary_existing_hint"),active:r&&i.bucket==="existing",onClick:()=>t("existing")}),n(s,{label:e("summary_conflicts"),value:a,tone:a>0?"info":"neutral",icon:"hand-pointer",hint:a>0?e("summary_conflicts_hint"):e("summary_conflicts_none"),active:i.tab==="decisions",onClick:()=>c("decisions")}),n(s,{label:e("summary_folders"),value:o.folders,tone:"primary",icon:"folder-tree",hint:e("summary_folders_hint",{from:o.sourcecourses,to:o.targetcourses}),active:i.tab==="folders",onClick:()=>c("folders")})]})};var y=l;export{y as default};
