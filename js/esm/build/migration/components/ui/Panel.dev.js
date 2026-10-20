var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A section of a page: a card with a title, an icon, how many things it holds and, when it has them, its own actions.
 *
 * @module     local_filebank/migration/components/ui/Panel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
const Panel = /* @__PURE__ */ __name(({ title, icon, count, actions, flush = false, region, children }) => /* @__PURE__ */ jsxDEV("section", { className: "card", "data-region": region, children: [
  /* @__PURE__ */ jsxDEV("header", { className: "card-header d-flex flex-wrap align-items-center justify-content-between", children: [
    /* @__PURE__ */ jsxDEV("h3", { className: "card-title h5 mb-0 d-flex align-items-center", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-panel-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: icon, gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
        lineNumber: 29,
        columnNumber: 46
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
        lineNumber: 29,
        columnNumber: 13
      }),
      title,
      count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge rounded-pill fbm-count", children: count }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
        lineNumber: 31,
        columnNumber: 37
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
      lineNumber: 28,
      columnNumber: 9
    }),
    actions && /* @__PURE__ */ jsxDEV("div", { className: "fbm-panel-actions", children: actions }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
      lineNumber: 33,
      columnNumber: 21
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
    lineNumber: 27,
    columnNumber: 5
  }),
  /* @__PURE__ */ jsxDEV("div", { className: flush ? "card-body p-0" : "card-body", children }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
    lineNumber: 35,
    columnNumber: 5
  })
] }, void 0, true, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/Panel.tsx",
  lineNumber: 26,
  columnNumber: 95
}), "Panel");
var Panel_default = Panel;
export {
  Panel_default as default
};
//# sourceMappingURL=Panel.dev.js.map
