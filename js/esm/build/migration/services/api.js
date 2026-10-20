import{fetchOne as m}from"@moodle/lms/core/ajax";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the migration screens, in one place.
 *
 * @module     local_filebank/migration/services/api
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=(e,r)=>m({methodname:"local_filebank_migration_"+e,args:r}),c=e=>Object.keys(e).map(r=>({key:r,value:e[r]})),s=e=>({sourcefilters:c(e.source),targetfilters:c(e.target),rules:JSON.stringify(e.rules)}),u=(e,r,t,i)=>o("list",{search:e,state:r,page:t,perpage:i}),a=(e="",r=0)=>o("options",{search:e,profileid:r}),l=e=>o("check",{profileid:e.profileid,...s(e)}),p=e=>o("create",{profileid:e.profileid,...s(e)}),b=e=>o("get",{id:e}),P=(e,r,t,i,n)=>o("files",{id:e,bucket:r,search:t,page:i,perpage:n}),d=(e,r,t,i)=>o("folders",{id:e,search:r,page:t,perpage:i}),M=(e,r,t)=>o("reconfigure",{id:e,digest:r,...s(t)}),f=(e,r,t,i=[],n=[])=>o("select",{id:e,digest:r,action:t,visible:i,included:n}),x=(e,r,t,i)=>o("select",{id:e,digest:r,action:"group",groupcourseid:t.sourcecourseid,groupcmid:t.sourcecmid,groupincluded:i?1:0}),F=(e,r)=>o("execute",{id:e,digest:r}),k=(e,r)=>o("cancel",{id:e,runid:r}),O=e=>o("delete",{ids:e});export{k as cancelRun,l as checkMigration,p as createMigration,O as deleteMigrations,F as executeMigration,P as getFiles,d as getFolders,b as getMigration,a as getOptions,u as listMigrations,M as reconfigureMigration,f as selectFiles,x as selectFolder};
