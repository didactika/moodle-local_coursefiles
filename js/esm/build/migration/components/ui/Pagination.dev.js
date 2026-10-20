var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar that goes from one page of a list to another, without leaving the page.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { pageWindow } from "../../lib/paging";
import Icon from "./Icon";
const Pagination = /* @__PURE__ */ __name(({ page, perpage, total, onPage, text }) => {
  const bar = pageWindow(page, perpage, total);
  if (bar.pages < 2) {
    return null;
  }
  const go = /* @__PURE__ */ __name((target, title, content, key) => /* @__PURE__ */ jsxDEV("li", { children: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-page", title, "aria-label": title, onClick: () => onPage(target), children: content }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
    lineNumber: 31,
    columnNumber: 9
  }) }, key, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
    lineNumber: 30,
    columnNumber: 84
  }), "go");
  return /* @__PURE__ */ jsxDEV("nav", { "aria-label": text("pagination_label"), className: "fbm-pagination", children: [
    /* @__PURE__ */ jsxDEV("ul", { className: "fbm-pages", children: [
      bar.first && go(0, text("pagination_first"), /* @__PURE__ */ jsxDEV(Icon, { name: "angles-left" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 36,
        columnNumber: 59
      }), "first"),
      page > 0 && go(page - 1, text("pagination_previous"), /* @__PURE__ */ jsxDEV(Icon, { name: "angle-left" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 37,
        columnNumber: 68
      }), "previous"),
      bar.startDots && /* @__PURE__ */ jsxDEV("li", { children: /* @__PURE__ */ jsxDEV("span", { className: "fbm-page is-gap", children: "..." }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 38,
        columnNumber: 47
      }) }, "start", false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 38,
        columnNumber: 31
      }),
      bar.shown.map((index) => /* @__PURE__ */ jsxDEV("li", { children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: `fbm-page${index === page ? " is-current" : ""}`,
          onClick: () => onPage(index),
          "aria-current": index === page ? "page" : void 0,
          children: index + 1
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
          lineNumber: 40,
          columnNumber: 17
        }
      ) }, index, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 39,
        columnNumber: 39
      })),
      bar.endDots && /* @__PURE__ */ jsxDEV("li", { children: /* @__PURE__ */ jsxDEV("span", { className: "fbm-page is-gap", children: "..." }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 49,
        columnNumber: 43
      }) }, "end", false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 49,
        columnNumber: 29
      }),
      bar.last && go(bar.pages - 1, text("pagination_last"), bar.pages, "last"),
      page < bar.pages - 1 && go(page + 1, text("pagination_next"), /* @__PURE__ */ jsxDEV(Icon, { name: "angle-right" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 51,
        columnNumber: 76
      }), "next")
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 35,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-muted fbm-pagination-info", children: text("pagination_info", { current: page + 1, total: bar.pages, count: total }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 53,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
    lineNumber: 34,
    columnNumber: 12
  });
}, "Pagination");
var Pagination_default = Pagination;
export {
  Pagination_default as default
};
//# sourceMappingURL=Pagination.dev.js.map
