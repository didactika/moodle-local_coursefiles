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
 */const o=(e,r)=>m({methodname:"local_filebank_migration_"+e,args:r}),c=e=>Object.keys(e).map(r=>({key:r,value:e[r]})),s=e=>({sourcefilters:c(e.source),targetfilters:c(e.target),rules:JSON.stringify(e.rules)}),a=(e,r,i,t)=>o("list",{search:e,state:r,page:i,perpage:t}),l=()=>o("options",{}),b=e=>o("check",{profileid:e.profileid,...s(e)}),p=e=>o("create",{profileid:e.profileid,...s(e)}),P=e=>o("get",{id:e}),d=(e,r,i,t,n)=>o("decisions",{id:e,kind:r,nature:i,page:t,perpage:n}),x=(e,r,i,t,n)=>o("files",{id:e,bucket:r,search:i,page:t,perpage:n}),M=(e,r,i,t)=>o("folders",{id:e,search:r,page:i,perpage:t}),f=(e,r,i,t,n,g)=>o("resolve",{id:e,digest:r,kind:i,nature:t,ids:n,choice:g}),F=(e,r,i)=>o("reconfigure",{id:e,digest:r,...s(i)}),k=(e,r,i,t=[],n=[])=>o("select",{id:e,digest:r,action:i,visible:t,included:n}),O=(e,r,i,t)=>o("select",{id:e,digest:r,action:"group",groupcourseid:i.sourcecourseid,groupcmid:i.sourcecmid,groupincluded:t?1:0}),h=(e,r)=>o("execute",{id:e,digest:r}),y=(e,r)=>o("cancel",{id:e,runid:r}),D=e=>o("delete",{ids:e});export{y as cancelRun,b as checkMigration,p as createMigration,D as deleteMigrations,h as executeMigration,d as getDecisions,x as getFiles,M as getFolders,P as getMigration,l as getOptions,a as listMigrations,F as reconfigureMigration,f as resolveDecisions,k as selectFiles,O as selectFolder};
