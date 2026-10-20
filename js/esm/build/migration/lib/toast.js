import{requireAsync as e}from"@moodle/lms/core/amd";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Typed adapter for the toasts of Moodle, which have no native ESM module yet.
 *
 * @module     local_filebank/migration/lib/toast
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */let s=null;const a=async()=>{const o=await e("core/toast");return"default"in o?o.default:o},i=async(o,t="success")=>{s??=a(),await(await s).add(o,{type:t,closeButton:!0})};export{i as addToast};
