var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The head of a page: its title, a line that says what it is for and the actions that belong to the whole page.
 *
 * @module     local_filebank/migration/components/ui/PageHeader
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const PageHeader = /* @__PURE__ */ __name(({ title, subtitle, badges, back, actions }) => /* @__PURE__ */ jsxDEV("header", { className: "fbm-header", children: [
  /* @__PURE__ */ jsxDEV("div", { className: "fbm-header-title", children: [
    back,
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-header-line", children: [
      /* @__PURE__ */ jsxDEV("h2", { className: "h2", children: title }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
        lineNumber: 28,
        columnNumber: 13
      }),
      badges
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
      lineNumber: 27,
      columnNumber: 9
    }),
    subtitle && /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-header-subtitle", children: subtitle }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
      lineNumber: 31,
      columnNumber: 22
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
    lineNumber: 25,
    columnNumber: 5
  }),
  actions && /* @__PURE__ */ jsxDEV("div", { className: "fbm-header-actions", children: actions }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
    lineNumber: 33,
    columnNumber: 17
  })
] }, void 0, true, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/PageHeader.tsx",
  lineNumber: 24,
  columnNumber: 83
}), "PageHeader");
var PageHeader_default = PageHeader;
export {
  PageHeader_default as default
};
//# sourceMappingURL=PageHeader.dev.js.map
