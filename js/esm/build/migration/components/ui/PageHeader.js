import{jsx as e,jsxs as a}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The head of a page: its title, a line that says what it is for and the actions that belong to the whole page.
 *
 * @module     local_filebank/migration/components/ui/PageHeader
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const m=({title:s,subtitle:d,badges:c,back:r,actions:t})=>a("header",{className:"fbm-header",children:[a("div",{className:"fbm-header-title",children:[r,a("div",{className:"fbm-header-line",children:[e("h2",{className:"h2",children:s}),c]}),d&&e("p",{className:"text-muted fbm-header-subtitle",children:d})]}),t&&e("div",{className:"fbm-header-actions",children:t})]});var o=m;export{o as default};
