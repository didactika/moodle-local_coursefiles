import i from"../ui/Alert";import f from"../ui/EmptyState";import s from"../ui/Icon";import p from"../ui/Skeleton";import t from"../ui/StatTile";import{jsx as o,jsxs as n}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a migration with the chosen parameters would do, counted before anything is stored. It never says that something failed:
 * what does not match is listed as information and what needs a decision is only counted.
 *
 * @module     local_filebank/migration/components/parameters/CheckResult
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const u=({summary:e,loading:a,error:c,text:l})=>c!==""?o(i,{tone:"danger",live:!0,children:c}):a||!e?o(p,{rows:3,label:l("loading")}):e.files===0?o(f,{plain:!0,icon:"folder-open",title:l("check_nothing_title"),text:l("check_nothing_text")}):n("div",{className:"fbm-check-result","data-region":"check-result",children:[n("div",{className:"fbm-tiles",children:[o(t,{label:l("summary_tocopy"),value:e.tocopy,tone:"success",icon:"copy"}),o(t,{label:l("summary_tooverwrite"),value:e.tooverwrite,tone:"warning",icon:"pen-to-square"}),o(t,{label:l("summary_existing"),value:e.existing,tone:"neutral",icon:"equals"}),o(t,{label:l("summary_conflicts"),value:e.conflicts+e.openissues,tone:"info",icon:"hand-pointer"})]}),n("p",{className:"fbm-muted fbm-check-scope",children:[o(s,{name:"folder-tree",gap:!0}),l("check_scope",{folders:e.folders,from:e.sourcecourses,to:e.targetcourses})]}),!e.haswork&&o(i,{tone:"info",children:l("check_no_work")}),e.conflicts+e.openissues>0&&o(i,{tone:"info",children:l("check_decisions_later")}),e.notes.length>0&&o("ul",{className:"fbm-notes",children:e.notes.map(r=>n("li",{children:[o(s,{name:"circle-info",gap:!0}),r.label]},r.kind))})]});var b=u;export{b as default};
