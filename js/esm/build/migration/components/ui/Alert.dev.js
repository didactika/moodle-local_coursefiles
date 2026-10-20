var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A message inside the page: something that went wrong, something that is good to know.
 *
 * @module     local_filebank/migration/components/ui/Alert
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
import { alertClasses } from "../../lib/tones";
const ICONS = {
  danger: "circle-exclamation",
  warning: "triangle-exclamation",
  info: "circle-info",
  success: "circle-check",
  neutral: "circle-info",
  primary: "circle-info"
};
const Alert = /* @__PURE__ */ __name(({ tone = "info", children, actions, live = false }) => /* @__PURE__ */ jsxDEV(
  "div",
  {
    className: `${alertClasses(tone)} fbm-alert`,
    role: live ? "alert" : "status",
    children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-alert-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: ICONS[tone] }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Alert.tsx",
        lineNumber: 38,
        columnNumber: 38
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Alert.tsx",
        lineNumber: 38,
        columnNumber: 5
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-alert-body", children }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Alert.tsx",
        lineNumber: 39,
        columnNumber: 5
      }),
      actions && /* @__PURE__ */ jsxDEV("div", { className: "fbm-alert-actions", children: actions }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Alert.tsx",
        lineNumber: 40,
        columnNumber: 17
      })
    ]
  },
  void 0,
  true,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Alert.tsx",
    lineNumber: 34,
    columnNumber: 81
  }
), "Alert");
var Alert_default = Alert;
export {
  Alert_default as default
};
//# sourceMappingURL=Alert.dev.js.map
