/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The parameters of a migration: where they start from, the steps that ask for them and when each step is complete.
 *
 * @module     local_filebank/migration/lib/parameters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const u=20,a=200,f=["profile","courses","folders","sections","policies","check"],d=()=>({sourcetype:"folder",sourcefield:"name",sourcemethod:"pattern",sourcevalue:"~^(?<name>.+)$~",targetfield:"name",targetmethod:"template",targetvalue:"${name}"}),m=()=>({sourcetype:"folder",sourcefield:"name",sourcemethod:"equals",sourcevalue:"",targetfield:"name",targetmethod:"equals",targetvalue:""}),n=e=>JSON.parse(e.rules),g=e=>({profileid:e.id,source:{},target:{},rules:{...n(e),correspondences:[]}}),P=e=>{const s=r=>{const o={};return r.forEach(t=>{t.value!==""&&(o[t.key]=t.value)}),o};return{profileid:e.profileid,source:s(e.filters.source),target:s(e.filters.target),rules:JSON.parse(e.rules)}},l=e=>e.sourcevalue.trim()!==""&&e.targetvalue.trim()!=="",p=(e,s)=>{const r=[];if(e.profileid<=0&&r.push("profile"),s>0&&Object.keys(e.source).length+Object.keys(e.target).length===0&&r.push("courses"),(e.rules.correspondences.length===0||!e.rules.correspondences.every(l))&&r.push("correspondence"),e.rules.sectionstrategy==="mapping"){const o=e.rules.sectionmap;o.length>0&&o.every(c=>/^[1-9][0-9]*$/.test(c.source)&&/^[1-9][0-9]*$/.test(c.target))||r.push("sections")}return r},i={profile:"profile",courses:"courses",folders:"correspondence",sections:"sections"},y=(e,s,r)=>{const o=i[e];return o?p(s,r).filter(t=>t===o):[]},h=e=>JSON.stringify(e);export{u as MAX_CORRESPONDENCES,a as MAX_SECTION_PAIRS,f as STEP_KEYS,m as emptyCorrespondence,h as keyOf,g as parametersFor,P as parametersOf,p as problemsOf,n as profileRules,d as sameNameCorrespondence,y as stepProblems};
