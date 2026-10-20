var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A switch that turns something on or off, which a person tells from a check box because it takes effect at once.
 *
 * @module     local_filebank/migration/components/ui/Switch
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const Switch = /* @__PURE__ */ __name(({ checked, partial = false, label, onChange, disabled = false }) => /* @__PURE__ */ jsxDEV(
  "button",
  {
    type: "button",
    role: "switch",
    "aria-checked": partial ? "mixed" : checked,
    "aria-label": label,
    title: label,
    className: `fbm-switch${checked ? " is-on" : ""}${partial ? " is-partial" : ""}`,
    disabled,
    onClick: () => onChange(partial ? true : !checked),
    children: /* @__PURE__ */ jsxDEV("span", { className: "fbm-switch-knob" }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Switch.tsx",
      lineNumber: 32,
      columnNumber: 5
    })
  },
  void 0,
  false,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Switch.tsx",
    lineNumber: 22,
    columnNumber: 96
  }
), "Switch");
var Switch_default = Switch;
export {
  Switch_default as default
};
//# sourceMappingURL=Switch.dev.js.map
