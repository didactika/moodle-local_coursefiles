import s from"@moodle/lms/core/config";import n from"@moodle/lms/local_coursefiles/compat/fragment";/**
 * Authenticated backend requests shared by the typed feature actions.
 *
 * @module local_coursefiles/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=(t,o,e)=>new Promise((r,a)=>{n.loadFragment("local_coursefiles",t,o,e).then((l,m)=>r({html:l,js:m||""}),a)}),u=async t=>{const o=s.wwwroot+"/local/coursefiles/selection/status.php?"+new URLSearchParams({scope:t,sesskey:s.sesskey}),e=await fetch(o,{credentials:"same-origin"});return e.ok?await e.json():null};export{u as getImportStatus,p as loadFragment};
