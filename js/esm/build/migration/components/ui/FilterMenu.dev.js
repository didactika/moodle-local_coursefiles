var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The filters of a list: a button with the filter icon and the number of filters that are on floating over its corner, and a
 * drop-down with a title, a button that resets them and the choices of each filter as chips. A choice applies as soon as it is
 * made. It is the same drop-down that the pages drawn by the server use.
 *
 * @module     local_filebank/migration/components/ui/FilterMenu
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef } from "react";
import Icon from "./Icon";
const FilterMenu = /* @__PURE__ */ __name(({ label, resetLabel, groups }) => {
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
  return /* @__PURE__ */ jsxDEV(
    "details",
    {
      ref: box,
      className: "fbm-filterbox",
      "data-region": "filters",
      "data-active": applied.length > 0 ? "true" : void 0,
      children: [
        /* @__PURE__ */ jsxDEV("summary", { className: "btn btn-outline-secondary", children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "filter", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 71,
            columnNumber: 17
          }),
          label,
          applied.length > 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge rounded-pill fbm-count", children: applied.length }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 72,
            columnNumber: 40
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
          lineNumber: 70,
          columnNumber: 13
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-filterpanel", children: [
          /* @__PURE__ */ jsxDEV("div", { className: "fbm-filter-head", children: [
            /* @__PURE__ */ jsxDEV("h3", { className: "fbm-filter-heading", children: label }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 76,
              columnNumber: 21
            }),
            /* @__PURE__ */ jsxDEV(
              "button",
              {
                type: "button",
                className: "btn btn-link btn-icon",
                title: resetLabel,
                disabled: applied.length === 0,
                onClick: () => applied.forEach((group) => group.onChange("")),
                children: [
                  /* @__PURE__ */ jsxDEV(Icon, { name: "rotate-right" }, void 0, false, {
                    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                    lineNumber: 84,
                    columnNumber: 25
                  }),
                  /* @__PURE__ */ jsxDEV("span", { className: "filebank-visually-hidden", children: resetLabel }, void 0, false, {
                    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                    lineNumber: 84,
                    columnNumber: 53
                  })
                ]
              },
              void 0,
              true,
              {
                fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                lineNumber: 77,
                columnNumber: 21
              }
            )
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 75,
            columnNumber: 17
          }),
          groups.map((group) => /* @__PURE__ */ jsxDEV("fieldset", { className: "fbm-filter-group", children: [
            /* @__PURE__ */ jsxDEV("legend", { className: "fbm-filter-title", children: group.label }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 88,
              columnNumber: 21
            }),
            /* @__PURE__ */ jsxDEV("div", { className: "fbm-filter-chips", children: group.options.map((option) => /* @__PURE__ */ jsxDEV(
              "button",
              {
                type: "button",
                className: `btn btn-sm btn-outline-secondary fbm-chip${group.value === option.value ? " active" : ""}`,
                "aria-pressed": group.value === option.value,
                onClick: () => group.onChange(option.value),
                children: [
                  option.label,
                  option.count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "small", children: option.count }, void 0, false, {
                    fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                    lineNumber: 98,
                    columnNumber: 60
                  })
                ]
              },
              option.value,
              true,
              {
                fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
                lineNumber: 90,
                columnNumber: 56
              }
            )) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
              lineNumber: 89,
              columnNumber: 21
            })
          ] }, group.id, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
            lineNumber: 87,
            columnNumber: 40
          }))
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
          lineNumber: 74,
          columnNumber: 13
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/FilterMenu.tsx",
      lineNumber: 64,
      columnNumber: 12
    }
  );
}, "FilterMenu");
var FilterMenu_default = FilterMenu;
export {
  FilterMenu_default as default
};
//# sourceMappingURL=FilterMenu.dev.js.map
