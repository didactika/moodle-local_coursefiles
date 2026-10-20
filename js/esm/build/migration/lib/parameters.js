/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The parameters of a migration: where they start from and when they are complete.
 *
 * @module     local_filebank/migration/lib/parameters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=20,i=200,u=()=>({sourcetype:"folder",sourcefield:"name",sourcemethod:"pattern",sourcevalue:"~^(?<name>.+)$~",targetfield:"name",targetmethod:"template",targetvalue:"${name}"}),c=e=>JSON.parse(e.rules),a=e=>({profileid:e.id,source:{},target:{},rules:c(e)}),f=e=>{const o=r=>{const s={};return r.forEach(t=>{t.value!==""&&(s[t.key]=t.value)}),s};return{profileid:e.profileid,source:o(e.filters.source),target:o(e.filters.target),rules:JSON.parse(e.rules)}},l=e=>e.sourcevalue.trim()!==""&&e.targetvalue.trim()!=="",d=(e,o)=>{const r=[];if(e.profileid<=0&&r.push("profile"),o>0&&Object.keys(e.source).length+Object.keys(e.target).length===0&&r.push("courses"),(e.rules.correspondences.length===0||!e.rules.correspondences.every(l))&&r.push("correspondence"),e.rules.sectionstrategy==="mapping"){const s=e.rules.sectionmap;s.length>0&&s.every(n=>/^[1-9][0-9]*$/.test(n.source)&&/^[1-9][0-9]*$/.test(n.target))||r.push("sections")}return r},m=e=>JSON.stringify(e);export{p as MAX_CORRESPONDENCES,i as MAX_SECTION_PAIRS,m as keyOf,a as parametersFor,f as parametersOf,d as problemsOf,c as profileRules,u as sameNameCorrespondence};
