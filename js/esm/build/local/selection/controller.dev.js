var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Initialize the feature actions and event listeners.
 *
 * @module local_multipicker/local/selection/controller
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from "@moodle/lms/local_multipicker/local/selection/selectors";
import { createActions } from "@moodle/lms/local_multipicker/local/selection/actions";
import { registerListeners } from "@moodle/lms/local_multipicker/local/selection/listeners";
import { presentation } from "@moodle/lms/local_multipicker/local/picker/bridge";
const selection = /* @__PURE__ */ __name(() => {
  presentation();
  const form = document.querySelector(Selectors.selectionForm);
  if (!form) {
    return;
  }
  const actions = createActions(form);
  registerListeners(form, actions);
}, "selection");
export {
  selection
};
//# sourceMappingURL=controller.dev.js.map
