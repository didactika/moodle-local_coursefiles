var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A row of choices of which one is on, drawn as pills: the groups of files, the size of a page.
 *
 * @module     local_filebank/migration/components/ui/Chips
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const Chips = /* @__PURE__ */ __name(({ label, value, chips, onChange }) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-chips", role: "group", "aria-label": label, children: chips.map((chip) => /* @__PURE__ */ jsxDEV(
  "button",
  {
    type: "button",
    className: `btn btn-sm btn-outline-secondary rounded-pill fbm-chip${chip.value === value ? " active" : ""}`,
    "aria-pressed": chip.value === value,
    onClick: () => onChange(chip.value),
    children: [
      chip.label,
      chip.count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge rounded-pill fbm-chip-count", children: chip.count }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Chips.tsx",
        lineNumber: 36,
        columnNumber: 38
      })
    ]
  },
  chip.value,
  true,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Chips.tsx",
    lineNumber: 28,
    columnNumber: 26
  }
)) }, void 0, false, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/Chips.tsx",
  lineNumber: 27,
  columnNumber: 64
}), "Chips");
var Chips_default = Chips;
export {
  Chips_default as default
};
//# sourceMappingURL=Chips.dev.js.map
