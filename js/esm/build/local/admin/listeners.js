import l from"@moodle/lms/local_filebank/local/admin/selectors";import{listen as o}from"@moodle/lms/local_filebank/local/listeners";import{changePage as i}from"@moodle/lms/local_filebank/local/admin/actions";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * admin listeners.
 * @module local_filebank/local/admin/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const m=(t,e)=>{o(t,"change",a=>{const n=a.target;n instanceof HTMLInputElement&&n.matches(l.selectAll)?e.selectAllChange():n instanceof HTMLInputElement&&n.matches(l.bulkChecks)&&e.update()}),t.addEventListener("filebank:listchanged",e.update),o(t,"submit",e.submit)},u=(t,e)=>{t.querySelectorAll(l.versions).forEach(a=>o(a,"change",e.update))},p=()=>{document.body.dataset.filebankPagination||(document.body.dataset.filebankPagination="true",o(document,"change",i))};export{m as registerBulk,u as registerHistory,p as registerPagination};
