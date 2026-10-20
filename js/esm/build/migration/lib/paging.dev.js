var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which pages a bar of pages shows around the one that is open.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const pageWindow = /* @__PURE__ */ __name((page, perpage, total, around = 2) => {
  const pages = Math.max(1, Math.ceil(total / Math.max(1, perpage)));
  const start = Math.max(0, Math.min(page - around, pages - 1 - around * 2));
  const end = Math.min(pages - 1, Math.max(page + around, around * 2));
  const shown = [];
  for (let index = Math.max(0, start); index <= end; index++) {
    shown.push(index);
  }
  return {
    pages,
    shown,
    first: shown[0] > 0,
    last: shown[shown.length - 1] < pages - 1,
    startDots: shown[0] > 1,
    endDots: shown[shown.length - 1] < pages - 2
  };
}, "pageWindow");
export {
  pageWindow
};
//# sourceMappingURL=paging.dev.js.map
