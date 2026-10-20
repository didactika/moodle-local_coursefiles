import t from"@moodle/lms/local_filebank/local/admin/selectors";import{createBulkActions as o,createHistoryActions as i}from"@moodle/lms/local_filebank/local/admin/actions";import{registerBulk as s,registerHistory as c,registerPagination as m}from"@moodle/lms/local_filebank/local/admin/listeners";import{registerFilterMenus as n,registerLiveFilters as l}from"@moodle/lms/local_filebank/local/admin/filters";import{registerLists as a}from"@moodle/lms/local_filebank/local/admin/lists";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * admin controller.
 * @module local_filebank/local/admin/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const A=()=>{document.querySelectorAll(t.bulkForms).forEach(r=>{const e=o(r);e&&s(r,e)}),document.querySelectorAll(t.history).forEach(r=>{const e=i(r);e&&c(r,e)}),m(),l(),n(),a()};export{A as init};
