var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Filters that apply as they are set: a list, a date or a choice sends the form at once and what is typed does it a
 * moment after the last key, so there is no button to press. The page comes back with the cursor where it was, and a
 * drop-down of filters closes when the person clicks outside it or presses Escape.
 *
 * @module local_filebank/local/admin/filters
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const DELAY = 400;
const FOCUS = "local_filebank/live-focus";
const takeFocus = /* @__PURE__ */ __name(() => {
  try {
    const name = window.sessionStorage.getItem(FOCUS) ?? "";
    window.sessionStorage.removeItem(FOCUS);
    return name;
  } catch {
    return "";
  }
}, "takeFocus");
const registerLiveFilters = /* @__PURE__ */ __name(() => {
  const focused = takeFocus();
  document.querySelectorAll("form[data-filebank-live-filters]").forEach((form) => {
    if (form.dataset.ready) {
      return;
    }
    form.dataset.ready = "true";
    let timer = 0;
    const send = /* @__PURE__ */ __name(() => {
      window.clearTimeout(timer);
      const active = document.activeElement;
      if (active instanceof HTMLInputElement && form.contains(active) && active.name !== "") {
        try {
          window.sessionStorage.setItem(FOCUS, active.name);
        } catch {
        }
      }
      form.requestSubmit();
    }, "send");
    form.addEventListener("change", send);
    form.addEventListener("input", (event) => {
      if (event.target instanceof HTMLElement && event.target.matches("[data-filebank-live-text]")) {
        window.clearTimeout(timer);
        timer = window.setTimeout(send, DELAY);
      }
    });
    const field = focused !== "" ? form.querySelector(`input[name="${CSS.escape(focused)}"]`) : null;
    if (field) {
      field.focus();
      field.setSelectionRange(field.value.length, field.value.length);
    }
  });
}, "registerLiveFilters");
const registerFilterMenus = /* @__PURE__ */ __name(() => {
  if (document.body.dataset.filebankMenus) {
    return;
  }
  document.body.dataset.filebankMenus = "true";
  const close = /* @__PURE__ */ __name((keep) => {
    document.querySelectorAll("details.fbm-filterbox[open]").forEach((box) => {
      if (!(keep instanceof Node) || !box.contains(keep)) {
        box.open = false;
      }
    });
  }, "close");
  document.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest(".daterangepicker")) {
      return;
    }
    close(event.target);
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      close(null);
    }
  });
}, "registerFilterMenus");
export {
  registerFilterMenus,
  registerLiveFilters
};
//# sourceMappingURL=filters.dev.js.map
