import r from"@moodle/lms/local_coursefiles/local/admin/selectors";import{listen as o}from"@moodle/lms/local_coursefiles/local/listeners";import{changePage as n}from"@moodle/lms/local_coursefiles/local/admin/actions";/**
 * admin listeners.
 * @module local_coursefiles/local/admin/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const u=(e,t)=>{const l=e.querySelector(r.selectAll);l&&o(l,"change",t.selectAllChange),e.querySelectorAll(r.bulkChecks).forEach(c=>o(c,"change",t.update)),o(e,"submit",t.submit)},m=(e,t)=>{e.querySelectorAll(r.versions).forEach(l=>o(l,"change",t.update))},p=()=>{document.body.dataset.coursefilesPagination||(document.body.dataset.coursefilesPagination="true",o(document,"change",n))};export{u as registerBulk,m as registerHistory,p as registerPagination};
