import r from"@moodle/lms/core/config";import m from"@moodle/lms/local_filebank/compat/fragment";/**
 * Authenticated backend requests shared by the typed feature actions.
 *
 * @module local_filebank/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=(e,o,t)=>new Promise((a,s)=>{m.loadFragment("local_filebank",e,o,t).then((l,n)=>a({html:l,js:n||""}),s)}),d=async e=>{const o=r.wwwroot+"/local/filebank/selection/status.php?"+new URLSearchParams({scope:e,sesskey:r.sesskey}),t=await fetch(o,{credentials:"same-origin"});return t.ok?await t.json():null};export{d as getImportStatus,c as loadFragment};
