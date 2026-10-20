var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a list shows when it has nothing to list: what happened and what the person can do about it.
 *
 * @module     local_filebank/migration/components/ui/EmptyState
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
const EmptyState = /* @__PURE__ */ __name(({ icon, title, text, actions, plain = false }) => /* @__PURE__ */ jsxDEV(
  "div",
  {
    className: `fbm-empty${plain ? " is-plain" : ""}`,
    "data-region": "empty-state",
    children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-empty-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: icon }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
        lineNumber: 28,
        columnNumber: 37
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
        lineNumber: 28,
        columnNumber: 5
      }),
      /* @__PURE__ */ jsxDEV("h4", { className: "h4", children: title }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
        lineNumber: 29,
        columnNumber: 5
      }),
      /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-empty-text", children: text }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
        lineNumber: 30,
        columnNumber: 5
      }),
      actions && /* @__PURE__ */ jsxDEV("div", { className: "fbm-empty-actions", children: actions }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
        lineNumber: 31,
        columnNumber: 17
      })
    ]
  },
  void 0,
  true,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/EmptyState.tsx",
    lineNumber: 24,
    columnNumber: 86
  }
), "EmptyState");
var EmptyState_default = EmptyState;
export {
  EmptyState_default as default
};
//# sourceMappingURL=EmptyState.dev.js.map
