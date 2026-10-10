var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Initialize the feature actions and event listeners.
 *
 * @module local_multipicker/local/review/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from "@moodle/lms/local_multipicker/local/review/selectors";
import { createActions } from "@moodle/lms/local_multipicker/local/review/actions";
import { registerListeners } from "@moodle/lms/local_multipicker/local/review/listeners";
const init = /* @__PURE__ */ __name(() => {
  const form = document.querySelector(Selectors.reviewForm);
  if (!form) {
    return;
  }
  const actions = createActions(form);
  registerListeners(form, actions);
}, "init");
export {
  init
};
//# sourceMappingURL=controller.dev.js.map
