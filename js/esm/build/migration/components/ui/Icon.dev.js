var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * An icon of Font Awesome, which Moodle already loads, so it takes the colour and the size of the text around it.
 *
 * @module     local_filebank/migration/components/ui/Icon
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { iconClass } from "../../lib/icons";
const Icon = /* @__PURE__ */ __name(({ name, gap = false, spin = false }) => /* @__PURE__ */ jsxDEV(
  "i",
  {
    className: `${iconClass(name)}${gap ? " fbm-gap" : ""}${spin ? " fa-spin" : ""}`,
    "aria-hidden": "true"
  },
  void 0,
  false,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Icon.tsx",
    lineNumber: 23,
    columnNumber: 64
  }
), "Icon");
var Icon_default = Icon;
export {
  Icon_default as default
};
//# sourceMappingURL=Icon.dev.js.map
