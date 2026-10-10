var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * admin listeners.
 * @module local_coursefiles/local/admin/listeners
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from "@moodle/lms/local_coursefiles/local/admin/selectors";
import { listen } from "@moodle/lms/local_coursefiles/local/listeners";
import { changePage } from "@moodle/lms/local_coursefiles/local/admin/actions";
const registerBulk = /* @__PURE__ */ __name((form, actions) => {
  const all = form.querySelector(Selectors.selectAll);
  if (all) {
    listen(all, "change", actions.selectAllChange);
  }
  form.querySelectorAll(Selectors.bulkChecks).forEach((check) => listen(check, "change", actions.update));
  listen(form, "submit", actions.submit);
}, "registerBulk");
const registerHistory = /* @__PURE__ */ __name((history, actions) => {
  history.querySelectorAll(Selectors.versions).forEach((check) => listen(check, "change", actions.update));
}, "registerHistory");
const registerPagination = /* @__PURE__ */ __name(() => {
  if (!document.body.dataset.coursefilesPagination) {
    document.body.dataset.coursefilesPagination = "true";
    listen(document, "change", changePage);
  }
}, "registerPagination");
export {
  registerBulk,
  registerHistory,
  registerPagination
};
//# sourceMappingURL=listeners.dev.js.map
