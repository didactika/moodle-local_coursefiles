import{appendLinks as a}from"@moodle/lms/local_coursefiles/local/editor/actions";import{getImportStatus as s}from"@moodle/lms/local_coursefiles/local/repository";import{getInstanceForElementId as m}from"@moodle/lms/local_coursefiles/compat/editor";import e from"@moodle/lms/local_coursefiles/compat/notification";import{getString as f}from"@moodle/lms/core/stringUtils";/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_coursefiles/local/editor/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const g=async(i,o)=>{const n=Date.now()+2e4;try{for(;Date.now()<n;){const t=await s(i);if(!t)return;if(t.ready){if(!t.links||t.files.length<2)return;const r=m(o);if(!r){e.alert("",await f("editorimportfailed","local_coursefiles"));return}if(a(r,t))return}await new Promise(r=>setTimeout(r,200))}}catch(t){e.exception(t)}};export{g as watch};
