import{appendLinks as a}from"@moodle/lms/local_multipicker/local/editor/actions";import{getImportStatus as m}from"@moodle/lms/local_multipicker/local/repository";import{getInstanceForElementId as s}from"@moodle/lms/local_multipicker/compat/editor";import i from"@moodle/lms/local_multipicker/compat/notification";import{getString as c}from"@moodle/lms/core/stringUtils";/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_multipicker/local/editor/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const g=async(e,o)=>{const n=Date.now()+2e4;try{for(;Date.now()<n;){const t=await m(e);if(!t)return;if(t.ready){if(!t.links||t.files.length<2)return;const r=s(o);if(!r){i.alert("",await c("editorimportfailed","local_multipicker"));return}if(a(r,t))return}await new Promise(r=>setTimeout(r,200))}}catch(t){i.exception(t)}};export{g as watch};
