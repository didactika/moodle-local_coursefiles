var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A button that is only an icon, tinted with the colour of what it does. The label is read by assistive technology and is the title
 * of the button, so a row of them does not need a word on each.
 *
 * @module     local_filebank/migration/components/ui/IconButton
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
const IconButton = /* @__PURE__ */ __name(({ icon, label, onClick, tone = "neutral", disabled = false }) => /* @__PURE__ */ jsxDEV(
  "button",
  {
    type: "button",
    className: `btn btn-outline-secondary fbm-iconpill${tone === "neutral" ? "" : ` tone-${tone}`}`,
    title: label,
    disabled,
    onClick,
    children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: icon }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/IconButton.tsx",
        lineNumber: 32,
        columnNumber: 5
      }),
      /* @__PURE__ */ jsxDEV("span", { className: "filebank-visually-hidden", children: label }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/IconButton.tsx",
        lineNumber: 32,
        columnNumber: 25
      })
    ]
  },
  void 0,
  true,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/IconButton.tsx",
    lineNumber: 25,
    columnNumber: 101
  }
), "IconButton");
var IconButton_default = IconButton;
export {
  IconButton_default as default
};
//# sourceMappingURL=IconButton.dev.js.map
