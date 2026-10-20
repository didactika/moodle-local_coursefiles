import c from"../ui/EmptyState";import d from"../ui/Panel";import l from"./NatureCard";import{Fragment as u,jsx as t,jsxs as m}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Everything that waits for a decision in a migration, grouped by its nature. None of it stops the migration: what is not decided
 * stays out of it, and what is decided is saved in the same migration.
 *
 * @module     local_filebank/migration/components/review/DecisionsPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=({migration:e,text:n,busy:i,onResolve:o})=>{const r=e.summary.skipped>0;return t(d,{title:n("tab_decisions"),icon:"hand-pointer",count:e.natures.reduce((s,a)=>s+a.count,0),region:"decisions",children:e.natures.length===0?t(c,{plain:!0,icon:"circle-check",title:n("decisions_none_title"),text:n(r?"decisions_none_settled":"decisions_none_text")}):m(u,{children:[t("p",{className:"fbm-muted fbm-panel-intro",children:n("decisions_intro")}),t("div",{className:"fbm-natures",children:e.natures.map(s=>t(l,{migrationId:e.id,digest:e.digest,nature:s,text:n,busy:i||!e.editable,onResolve:o},s.kind+s.nature))})]})})};var _=p;export{_ as default};
