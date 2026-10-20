var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What went wrong in a call, as a sentence the person can read.
 *
 * @module     local_filebank/migration/lib/errors
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const describeError = /* @__PURE__ */ __name((error) => {
  if (typeof error === "string") {
    return error;
  }
  if (error !== null && typeof error === "object") {
    const found = error;
    return found.message ?? found.error ?? found.errorcode ?? "";
  }
  return "";
}, "describeError");
const isStale = /* @__PURE__ */ __name((error) => {
  if (error !== null && typeof error === "object") {
    return error.errorcode === "planstale";
  }
  return false;
}, "isStale");
export {
  describeError,
  isStale
};
//# sourceMappingURL=errors.dev.js.map
