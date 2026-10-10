var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_multipicker/local/picker/bridge
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from "@moodle/lms/local_multipicker/local/selectors";
import { listen, eventElement } from "@moodle/lms/local_multipicker/local/listeners";
let hostObject = null;
let hostObserver = null;
const presentation = /* @__PURE__ */ __name(() => {
  const browser = document.querySelector(Selectors.browser);
  if (browser) {
    browser.dataset.multipickerReady = "true";
  }
  if (window.parent === window) {
    return;
  }
  const object = [...window.parent.document.querySelectorAll(Selectors.hostObjects)].find((element) => element.contentDocument?.defaultView === window);
  const picker = object?.closest(".file-picker");
  if (!object || !picker || hostObject === object) {
    return;
  }
  picker.classList.add("multipicker-native-host");
  document.body.classList.add("multipicker-embedded");
  const surface = picker.querySelector(Selectors.pickerSurface);
  const background = window.parent.getComputedStyle(surface).backgroundColor;
  if (background !== "rgba(0, 0, 0, 0)") {
    document.body.style.setProperty("--multipicker-host-background", background);
  }
  const repositories = picker.querySelector(Selectors.repositories);
  const active = repositories?.querySelector(Selectors.activeRepository);
  const revealRepository = /* @__PURE__ */ __name(() => {
    if (active && window.parent.innerWidth < 576) {
      repositories.scrollLeft = active.offsetLeft - repositories.offsetLeft;
    }
  }, "revealRepository");
  revealRepository();
  listen(window.parent, "resize", revealRepository);
  hostObserver?.disconnect();
  hostObject = object;
  hostObserver = new MutationObserver(() => {
    if (!object.isConnected) {
      picker.classList.remove("multipicker-native-host");
      hostObserver?.disconnect();
      hostObject = null;
      window.parent.removeEventListener("resize", revealRepository);
    }
  });
  hostObserver.observe(picker, { childList: true, subtree: true });
  listen(window, "pagehide", () => {
    if (!object.isConnected) {
      picker.classList.remove("multipicker-native-host");
    }
    hostObserver?.disconnect();
    window.parent.removeEventListener("resize", revealRepository);
  }, { once: true });
}, "presentation");
const complete = /* @__PURE__ */ __name((file) => {
  const button = document.querySelector(Selectors.confirm);
  const supported = window.parent !== window && window.parent.M?.core_filepicker?.select_file;
  if (button && supported) {
    button.disabled = false;
  }
  document.querySelector(Selectors.reviewForm)?.addEventListener("input", (event) => {
    if (eventElement(event).matches(Selectors.reviewChecks)) {
      return;
    }
    if (button) {
      button.disabled = true;
    }
  });
  button?.addEventListener("click", () => {
    if (!supported) {
      return;
    }
    button.disabled = true;
    if (file.editorid) {
      window.parent.require(["local_multipicker/editor_import"], (bridge) => bridge.watch(file.scope, file.editorid));
    }
    window.parent.M.core_filepicker.select_file(file);
    const confirmation = window.parent.document.querySelector(Selectors.nativeConfirmation);
    if (confirmation) {
      confirmation.click();
    } else {
      button.disabled = false;
    }
  });
}, "complete");
export {
  complete,
  presentation
};
//# sourceMappingURL=bridge.dev.js.map
