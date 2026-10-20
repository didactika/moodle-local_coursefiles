var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A bar that says how much of something is done.
 *
 * @module     local_filebank/migration/components/ui/Progress
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const Progress = /* @__PURE__ */ __name(({ value, max, label, tone = "primary" }) => {
  const percent = max > 0 ? Math.min(100, Math.round(value / max * 100)) : 0;
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-progress tone-${tone}`, role: "progressbar", "aria-label": label, "aria-valuemin": 0, "aria-valuemax": max, "aria-valuenow": value, children: /* @__PURE__ */ jsxDEV("div", { className: "fbm-progress-fill", style: { width: `${percent}%` } }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Progress.tsx",
    lineNumber: 24,
    columnNumber: 9
  }) }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Progress.tsx",
    lineNumber: 23,
    columnNumber: 12
  });
}, "Progress");
var Progress_default = Progress;
export {
  Progress_default as default
};
//# sourceMappingURL=Progress.dev.js.map
