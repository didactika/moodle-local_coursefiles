import{useCallback as c,useEffect as y,useRef as S,useState as o}from"react";import{describeError as E,isStale as P}from"../lib/errors";import{getMigration as L}from"../services/api";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One migration as the screen shows it: it is read, kept in step with the server while it runs, and replaced by what every change
 * answers, because each answer carries the digest that the next change has to send.
 *
 * @module     local_filebank/migration/hooks/useMigration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const I=3e3,C=s=>{const[u,l]=o(null),[M,f]=o(!0),[w,g]=o(!1),[b,t]=o(""),[T,a]=o(!1),m=S(s),i=c(r=>{a(P(r)),t(E(r))},[]),e=c(async()=>{try{const r=await L(m.current);l(r),a(!1),t("")}catch(r){i(r)}finally{f(!1)}},[i]);y(()=>{m.current=s,l(null),f(!0),t(""),a(!1),e()},[s,e]);const d=u?.run?.active===!0;y(()=>{if(!d)return;const r=window.setInterval(()=>void e(),I);return()=>window.clearInterval(r)},[d,e]);const v=c(async(r,p)=>{g(!0),t("");try{const n=await r();return l(p?p(n):n),a(!1),n}catch(n){return i(n),null}finally{g(!1)}},[i]);return{migration:u,loading:M,busy:w,error:b,stale:T,clearError:()=>t(""),reload:e,apply:v}};export{C as useMigration};
