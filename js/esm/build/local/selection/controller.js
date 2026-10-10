import r from"@moodle/lms/local_coursefiles/local/selection/selectors";import{createActions as t}from"@moodle/lms/local_coursefiles/local/selection/actions";import{registerListeners as m}from"@moodle/lms/local_coursefiles/local/selection/listeners";import{presentation as n}from"@moodle/lms/local_coursefiles/local/picker/bridge";/**
 * Initialize the feature actions and event listeners.
 *
 * @module local_coursefiles/local/selection/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=()=>{n();const o=document.querySelector(r.selectionForm);if(!o)return;const e=t(o);m(o,e)};export{p as selection};
