var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The bar under a list: how many rows a page holds, the rows that are shown out of all, and the arrows to the previous and next page.
 * It is always at the bottom of the list, where the eye ends.
 *
 * @module     local_filebank/migration/components/ui/Pagination
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useId } from "react";
import { pageSizeChoices } from "../../lib/paging";
import Icon from "./Icon";
const Pagination = /* @__PURE__ */ __name(({ page, perpage, total, onPage, text, onPerPage }) => {
  const id = useId();
  const pages = Math.max(1, Math.ceil(total / Math.max(1, perpage)));
  const choose = onPerPage !== void 0 && total > 0 ? { sizes: pageSizeChoices(total), onPerPage } : null;
  const shown = choose === null || choose.sizes.includes(perpage) ? perpage : perpage >= total && choose.sizes.includes(total) ? total : choose.sizes[0];
  useEffect(() => {
    if (choose !== null && shown !== perpage) {
      choose.onPerPage(shown);
    }
  }, [shown, perpage]);
  if (pages < 2 && choose === null) {
    return null;
  }
  const from = total > 0 ? page * perpage + 1 : 0;
  const to = Math.min((page + 1) * perpage, total);
  const arrow = /* @__PURE__ */ __name((target, enabled, icon, title) => /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: "btn btn-link btn-icon fbm-pager-arrow",
      title,
      "aria-label": title,
      disabled: !enabled,
      onClick: () => onPage(target),
      children: /* @__PURE__ */ jsxDEV(Icon, { name: icon }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 54,
        columnNumber: 9
      })
    },
    void 0,
    false,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 46,
      columnNumber: 86
    }
  ), "arrow");
  return /* @__PURE__ */ jsxDEV("nav", { "aria-label": text("pagination_label"), className: "fbm-listbar fbm-pagination", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-listbar-size", children: choose !== null && /* @__PURE__ */ jsxDEV(Fragment, { children: [
      /* @__PURE__ */ jsxDEV("label", { className: "fbm-listbar-label", htmlFor: id, children: text("per_page") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 60,
        columnNumber: 17
      }),
      /* @__PURE__ */ jsxDEV(
        "select",
        {
          id,
          className: "form-select form-select-sm rounded-pill",
          value: shown,
          onChange: (event) => choose.onPerPage(Number(event.target.value)),
          children: choose.sizes.map((size) => /* @__PURE__ */ jsxDEV("option", { value: size, children: size === total ? text("all") : size }, size, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
            lineNumber: 67,
            columnNumber: 49
          }))
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
          lineNumber: 61,
          columnNumber: 17
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 59,
      columnNumber: 33
    }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 58,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-listbar-pages", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "text-muted small", "aria-live": "polite", children: text("pagination_range", { from, to, total }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 72,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-actions", children: [
        arrow(page - 1, page > 0, "chevron-left", text("pagination_previous")),
        arrow(page + 1, page < pages - 1, "chevron-right", text("pagination_next"))
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
        lineNumber: 73,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
      lineNumber: 71,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/Pagination.tsx",
    lineNumber: 57,
    columnNumber: 12
  });
}, "Pagination");
var Pagination_default = Pagination;
export {
  Pagination_default as default
};
//# sourceMappingURL=Pagination.dev.js.map
