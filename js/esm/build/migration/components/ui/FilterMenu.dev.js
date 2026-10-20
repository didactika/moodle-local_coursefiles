var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The filters of a list: a button with the filter icon and the number of filters that are on floating over its corner, and a
 * drop-down with a title, a button that resets them and the choices. A choice applies as soon as it is made, and the filters that
 * are on are also shown outside as chips that take one away. It is the same drop-down that the pages drawn by the server use.
 *
 * @module     local_filebank/migration/components/ui/FilterMenu
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef } from "react";
import Icon from "./Icon";
const FilterMenu = /* @__PURE__ */ __name(({ label, resetLabel, groups, afterSearch = true }) => {
  const box = useRef(null);
  const applied = groups.filter((group) => group.value !== "");
  useEffect(() => {
    const close = /* @__PURE__ */ __name((event) => {
      const element = box.current;
      if (element?.open && (!(event.target instanceof Node) || !element.contains(event.target))) {
        element.open = false;
      }
    }, "close");
    const escape = /* @__PURE__ */ __name((event) => {
      if (event.key === "Escape" && box.current) {
        box.current.open = false;
      }
    }, "escape");
    document.addEventListener("click", close);
    document.addEventListener("keydown", escape);
    return () => {
      document.removeEventListener("click", close);
      document.removeEventListener("keydown", escape);
    };
  }, []);
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV(
      "details",
      {
        ref: box,
        className: `fbm-filterbox${afterSearch ? " fbm-filter-end" : ""}`,
        "data-region": "filters",
        "data-active": applied.length > 0 ? "true" : void 0,
        children: [
          /* @__PURE__ */ jsxDEV("summary", { className: "btn btn-outline-secondary", children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "filter", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 74,
              columnNumber: 17
            }),
            label,
            applied.length > 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge rounded-pill fbm-count", children: applied.length }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 75,
              columnNumber: 40
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 73,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("div", { className: "fbm-filterpanel", children: [
            /* @__PURE__ */ jsxDEV("div", { className: "fbm-filter-head", children: [
              /* @__PURE__ */ jsxDEV("h3", { className: "fbm-filter-heading", children: label }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                lineNumber: 79,
                columnNumber: 21
              }),
              /* @__PURE__ */ jsxDEV(
                "button",
                {
                  type: "button",
                  className: "btn btn-outline-secondary fbm-iconpill",
                  title: resetLabel,
                  disabled: applied.length === 0,
                  onClick: () => applied.forEach((group) => group.onChange("")),
                  children: [
                    /* @__PURE__ */ jsxDEV(Icon, { name: "rotate-right" }, void 0, false, {
                      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                      lineNumber: 87,
                      columnNumber: 25
                    }),
                    /* @__PURE__ */ jsxDEV("span", { className: "filebank-visually-hidden", children: resetLabel }, void 0, false, {
                      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                      lineNumber: 87,
                      columnNumber: 53
                    })
                  ]
                },
                void 0,
                true,
                {
                  fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                  lineNumber: 80,
                  columnNumber: 21
                }
              )
            ] }, void 0, true, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 78,
              columnNumber: 17
            }),
            groups.map((group) => /* @__PURE__ */ jsxDEV("fieldset", { className: "fbm-filter-group", children: [
              /* @__PURE__ */ jsxDEV("legend", { className: "fbm-filter-title", children: group.label }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                lineNumber: 91,
                columnNumber: 21
              }),
              group.options.map((option) => /* @__PURE__ */ jsxDEV("label", { className: "fbm-filter-option", children: [
                /* @__PURE__ */ jsxDEV(
                  "input",
                  {
                    type: "radio",
                    name: `fbm-filter-${group.id}`,
                    checked: group.value === option.value,
                    onChange: () => group.onChange(option.value)
                  },
                  void 0,
                  false,
                  {
                    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                    lineNumber: 93,
                    columnNumber: 25
                  }
                ),
                /* @__PURE__ */ jsxDEV("span", { children: option.label }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                  lineNumber: 99,
                  columnNumber: 25
                }),
                option.count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "text-muted small", children: option.count }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                  lineNumber: 100,
                  columnNumber: 56
                })
              ] }, option.value, true, {
                fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                lineNumber: 92,
                columnNumber: 52
              }))
            ] }, group.id, true, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 90,
              columnNumber: 40
            }))
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 77,
            columnNumber: 13
          })
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
        lineNumber: 67,
        columnNumber: 9
      }
    ),
    applied.length > 0 && /* @__PURE__ */ jsxDEV("div", { className: "fbm-chips", "data-region": "active-filters", children: applied.map((group) => /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "btn btn-sm btn-outline-secondary rounded-pill fbm-chip",
        onClick: () => group.onChange(""),
        children: [
          group.label,
          ": ",
          group.options.find((option) => option.value === group.value)?.label,
          /* @__PURE__ */ jsxDEV(Icon, { name: "xmark" }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 113,
            columnNumber: 17
          })
        ]
      },
      group.id,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
        lineNumber: 106,
        columnNumber: 37
      }
    )) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
      lineNumber: 105,
      columnNumber: 32
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
    lineNumber: 66,
    columnNumber: 12
  });
}, "FilterMenu");
var FilterMenu_default = FilterMenu;
export {
  FilterMenu_default as default
};
//# sourceMappingURL=FilterMenu.dev.js.map
