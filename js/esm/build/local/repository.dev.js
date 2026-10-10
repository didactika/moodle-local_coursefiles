var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Authenticated backend requests shared by the typed feature actions.
 *
 * @module local_filebank/local/repository
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Config from "@moodle/lms/core/config";
import Fragment from "@moodle/lms/local_filebank/compat/fragment";
const loadFragment = /* @__PURE__ */ __name((name, contextid, args) => new Promise((resolve, reject) => {
  Fragment.loadFragment("local_filebank", name, contextid, args).then((html, js) => resolve({ html, js: js || "" }), reject);
}), "loadFragment");
const getImportStatus = /* @__PURE__ */ __name(async (scope) => {
  const url = Config.wwwroot + "/local/filebank/selection/status.php?" + new URLSearchParams({ scope, sesskey: Config.sesskey });
  const response = await fetch(url, { credentials: "same-origin" });
  return response.ok ? await response.json() : null;
}, "getImportStatus");
export {
  getImportStatus,
  loadFragment
};
//# sourceMappingURL=repository.dev.js.map
