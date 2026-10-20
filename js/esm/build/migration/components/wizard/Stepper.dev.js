var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The steps of the page that starts a migration, listed down the left side: the ones that are done, the one that is open and the
 * ones that are still to come. A step that was reached can be opened again; one that was not cannot be skipped to.
 *
 * @module     local_filebank/migration/components/wizard/Stepper
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "../ui/Icon";
const Stepper = /* @__PURE__ */ __name(({ steps, current, reached, onGo, text }) => /* @__PURE__ */ jsxDEV("nav", { className: "fbm-stepper", "aria-label": text("wizard_steps"), children: /* @__PURE__ */ jsxDEV("ol", { className: "fbm-stepper-list", children: steps.map((step, index) => {
  const state = index === current ? "is-current" : index < current || index <= reached ? "is-done" : "is-todo";
  const open = index <= reached;
  return /* @__PURE__ */ jsxDEV("li", { className: `fbm-stepper-item ${state}`, children: /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: "fbm-stepper-button",
      disabled: !open,
      "aria-current": index === current ? "step" : void 0,
      onClick: () => onGo(index),
      "data-step": step,
      children: [
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-stepper-dot", children: state === "is-done" ? /* @__PURE__ */ jsxDEV(Icon, { name: "check" }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
          lineNumber: 40,
          columnNumber: 78
        }) : index + 1 }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
          lineNumber: 40,
          columnNumber: 21
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-stepper-text", children: [
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-stepper-title", children: text("step_" + step) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
            lineNumber: 42,
            columnNumber: 25
          }),
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-stepper-hint", children: text("step_" + step + "_short") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
            lineNumber: 43,
            columnNumber: 25
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
          lineNumber: 41,
          columnNumber: 21
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
      lineNumber: 32,
      columnNumber: 17
    }
  ) }, step, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
    lineNumber: 31,
    columnNumber: 20
  });
}) }, void 0, false, {
  fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
  lineNumber: 27,
  columnNumber: 5
}) }, void 0, false, {
  fileName: "public/local/filebank/js/esm/src/migration/components/wizard/Stepper.tsx",
  lineNumber: 26,
  columnNumber: 74
}), "Stepper");
var Stepper_default = Stepper;
export {
  Stepper_default as default
};
//# sourceMappingURL=Stepper.dev.js.map
