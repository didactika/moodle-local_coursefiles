var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A figure with its label: how many files would be copied, how many wait for a decision. It is a button when the figure leads
 * somewhere, such as the list of the things it counts.
 *
 * @module     local_filebank/migration/components/ui/StatTile
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
const StatTile = /* @__PURE__ */ __name(({ label, value, tone = "neutral", icon, hint, onClick, active = false }) => {
  const body = /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("span", { className: "fbm-tile-top", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-tile-label", children: label }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
        lineNumber: 32,
        columnNumber: 13
      }),
      icon && /* @__PURE__ */ jsxDEV("span", { className: "fbm-tile-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: icon }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
        lineNumber: 33,
        columnNumber: 54
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
        lineNumber: 33,
        columnNumber: 22
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
      lineNumber: 31,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("span", { className: "fbm-tile-value", children: value }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
      lineNumber: 35,
      columnNumber: 9
    }),
    hint && /* @__PURE__ */ jsxDEV("span", { className: "fbm-tile-hint", children: hint }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
      lineNumber: 36,
      columnNumber: 18
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
    lineNumber: 30,
    columnNumber: 18
  });
  const className = `fbm-tile tone-${tone}${active ? " is-active" : ""}${onClick ? " is-link" : ""}`;
  return onClick ? /* @__PURE__ */ jsxDEV("button", { type: "button", className, onClick, "aria-pressed": active, children: body }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
    lineNumber: 40,
    columnNumber: 11
  }) : /* @__PURE__ */ jsxDEV("div", { className, children: body }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatTile.tsx",
    lineNumber: 41,
    columnNumber: 11
  });
}, "StatTile");
var StatTile_default = StatTile;
export {
  StatTile_default as default
};
//# sourceMappingURL=StatTile.dev.js.map
