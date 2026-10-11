var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Native picker entry and documented embedded-chooser completion.
 *
 * @module local_filebank/local/picker/bridge
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from "@moodle/lms/local_filebank/local/selectors";
import { listen, eventElement } from "@moodle/lms/local_filebank/local/listeners";
let hostObject = null;
const presentation = /* @__PURE__ */ __name(() => {
  const browser = document.querySelector(Selectors.browser);
  if (browser) {
    browser.dataset.filebankReady = "true";
  }
  if (window.parent === window) {
    return;
  }
  const parent = window.parent;
  const object = [...parent.document.querySelectorAll(Selectors.hostObjects)].find((element) => element.contentDocument?.defaultView === window);
  const picker = object?.closest(".file-picker");
  if (!object || !picker || hostObject === object) {
    return;
  }
  object.filebankPresentationCleanup?.();
  picker.classList.add("filebank-native-host");
  document.body.classList.add("filebank-embedded");
  const surface = picker.querySelector(Selectors.pickerSurface);
  const background = parent.getComputedStyle(surface).backgroundColor;
  if (background !== "rgba(0, 0, 0, 0)") {
    document.body.style.setProperty("--filebank-host-background", background);
  }
  const repositories = picker.querySelector(Selectors.repositories);
  const active = repositories?.querySelector(Selectors.activeRepository);
  const revealRepository = /* @__PURE__ */ __name(() => {
    if (active && parent.innerWidth < 576) {
      repositories.scrollLeft = active.offsetLeft - repositories.offsetLeft;
    }
  }, "revealRepository");
  revealRepository();
  listen(parent, "resize", revealRepository);
  hostObject = object;
  const observer = new parent.MutationObserver(() => {
    if (!object.isConnected) {
      release();
    }
  });
  const release = /* @__PURE__ */ __name(() => {
    observer.disconnect();
    parent.removeEventListener("resize", revealRepository);
    picker.classList.remove("filebank-native-host");
    hostObject = null;
    delete object.filebankPresentationCleanup;
  }, "release");
  object.filebankPresentationCleanup = release;
  observer.observe(surface, { childList: true });
  listen(window, "pagehide", () => {
    parent.removeEventListener("resize", revealRepository);
    if (!object.isConnected) {
      release();
    }
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
      window.parent.require(["local_filebank/editor_import"], (bridge) => bridge.watch(file.scope, file.editorid));
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
