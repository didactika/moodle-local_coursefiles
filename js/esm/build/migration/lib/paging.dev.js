var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The sizes a page of a list can have, as block_course_finder offers them: the ones smaller than the list, and the whole list when
 * it is not too long, so a page never loads more than the person asked for and "All" never loads a long list at once.
 *
 * @module     local_filebank/migration/lib/paging
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const PAGE_SIZES = [12, 24, 48, 96];
const MAX_ALL = 500;
const pageSizeChoices = /* @__PURE__ */ __name((total) => {
  if (total <= PAGE_SIZES[0]) {
    return [Math.max(1, total)];
  }
  const sizes = PAGE_SIZES.filter((size) => size < total);
  return total <= MAX_ALL ? [...sizes, total] : sizes;
}, "pageSizeChoices");
export {
  MAX_ALL,
  PAGE_SIZES,
  pageSizeChoices
};
//# sourceMappingURL=paging.dev.js.map
