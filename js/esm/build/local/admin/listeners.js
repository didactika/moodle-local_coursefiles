import l from"@moodle/lms/local_filebank/local/admin/selectors";import{listen as n}from"@moodle/lms/local_filebank/local/listeners";import{changePage as i}from"@moodle/lms/local_filebank/local/admin/actions";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * admin listeners.
 * @module local_filebank/local/admin/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const m=(t,e)=>{n(t,"change",a=>{const o=a.target;o instanceof HTMLInputElement&&o.matches(l.selectAll)?e.selectAllChange():o instanceof HTMLInputElement&&o.matches(l.bulkChecks)&&e.update()}),n(t,"filebank:listchanged",e.update),n(t,"submit",e.submit)},u=(t,e)=>{t.querySelectorAll(l.versions).forEach(a=>n(a,"change",e.update))},p=()=>{document.body.dataset.filebankPagination||(document.body.dataset.filebankPagination="true",n(document,"change",i))};export{m as registerBulk,u as registerHistory,p as registerPagination};
