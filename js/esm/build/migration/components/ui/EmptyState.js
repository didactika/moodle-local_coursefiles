import p from"./Icon";import{jsx as t,jsxs as c}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a list shows when it has nothing to list: what happened and what the person can do about it.
 *
 * @module     local_filebank/migration/components/ui/EmptyState
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=({icon:a,title:m,text:i,actions:e,plain:s=!1})=>c("div",{className:`fbm-empty${s?" is-plain":""}`,"data-region":"empty-state",children:[t("div",{className:"fbm-empty-icon",children:t(p,{name:a})}),t("h4",{className:"h4",children:m}),t("p",{className:"text-muted fbm-empty-text",children:i}),e&&t("div",{className:"fbm-empty-actions",children:e})]});var d=o;export{d as default};
