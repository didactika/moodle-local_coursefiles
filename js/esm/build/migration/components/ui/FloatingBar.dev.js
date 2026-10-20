var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What can be done with what was chosen. It floats at the bottom of the screen while there is a choice, so the page does not move
 * and the actions are always at hand.
 *
 * @module     local_filebank/migration/components/ui/FloatingBar
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
const FloatingBar = /* @__PURE__ */ __name(({ summary, children, onClear, clearLabel }) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-floating", role: "status", "data-region": "bulk-actions", children: [
  /* @__PURE__ */ jsxDEV("span", { className: "fbm-floating-summary", children: summary }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
    lineNumber: 25,
    columnNumber: 5
  }),
  /* @__PURE__ */ jsxDEV("div", { className: "fbm-floating-actions", children }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
    lineNumber: 26,
    columnNumber: 5
  }),
  /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-floating-clear", onClick: onClear, title: clearLabel, children: [
    /* @__PURE__ */ jsxDEV(Icon, { name: "xmark" }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
      lineNumber: 28,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("span", { className: "filebank-visually-hidden", children: clearLabel }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
      lineNumber: 28,
      columnNumber: 30
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
    lineNumber: 27,
    columnNumber: 5
  })
] }, void 0, true, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/FloatingBar.tsx",
  lineNumber: 24,
  columnNumber: 85
}), "FloatingBar");
var FloatingBar_default = FloatingBar;
export {
  FloatingBar_default as default
};
//# sourceMappingURL=FloatingBar.dev.js.map
