import t from"@moodle/lms/local_filebank/local/admin/selectors";import{createBulkActions as o,createHistoryActions as i}from"@moodle/lms/local_filebank/local/admin/actions";import{registerBulk as s,registerHistory as c,registerPagination as n}from"@moodle/lms/local_filebank/local/admin/listeners";import{registerFilterMenus as l,registerLiveFilters as m}from"@moodle/lms/local_filebank/local/admin/filters";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * admin controller.
 * @module local_filebank/local/admin/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=()=>{document.querySelectorAll(t.bulkForms).forEach(e=>{const r=o(e);r&&s(e,r)}),document.querySelectorAll(t.history).forEach(e=>{const r=i(e);r&&c(e,r)}),n(),m(),l()};export{p as init};
