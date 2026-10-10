import r from"@moodle/lms/core/config";import i from"@moodle/lms/local_multipicker/compat/fragment";/**
 * Authenticated backend requests shared by the typed feature actions.
 *
 * @module local_multipicker/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const c=(e,o,t)=>new Promise((s,m)=>{i.loadFragment("local_multipicker",e,o,t).then((a,l)=>s({html:a,js:l||""}),m)}),u=async e=>{const o=r.wwwroot+"/local/multipicker/selection/status.php?"+new URLSearchParams({scope:e,sesskey:r.sesskey}),t=await fetch(o,{credentials:"same-origin"});return t.ok?await t.json():null};export{u as getImportStatus,c as loadFragment};
