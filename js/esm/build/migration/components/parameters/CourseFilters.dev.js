var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The courses a migration goes between: what the courses of each side have to have, one field for each filter of the profile.
 *
 * @module     local_filebank/migration/components/parameters/CourseFilters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "../ui/Icon";
const CourseFilters = /* @__PURE__ */ __name(({ dimensions, parameters, onChange, text, missing }) => {
  const set = /* @__PURE__ */ __name((side, key, value) => {
    const next = { ...parameters[side] };
    if (value === "") {
      delete next[key];
    } else {
      next[key] = value;
    }
    onChange({ ...parameters, [side]: next });
  }, "set");
  if (dimensions.length === 0) {
    return /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted", children: text("wizard_no_filters") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
      lineNumber: 36,
      columnNumber: 16
    });
  }
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-sides${missing ? " has-problem" : ""}`, children: [
    ["source", "target"].map((side) => /* @__PURE__ */ jsxDEV("fieldset", { className: `fbm-side fbm-side-${side}`, children: [
      /* @__PURE__ */ jsxDEV("legend", { className: "fbm-side-title", children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: side === "source" ? "arrow-right-from-bracket" : "arrow-right-to-bracket", gap: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
          lineNumber: 42,
          columnNumber: 17
        }),
        text(side === "source" ? "wizard_from" : "wizard_to")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
        lineNumber: 41,
        columnNumber: 13
      }),
      dimensions.map((dimension) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "fbm-label", htmlFor: `fbm-${side}-${dimension.key}`, children: dimension.label }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
          lineNumber: 46,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            id: `fbm-${side}-${dimension.key}`,
            type: "text",
            className: "fbm-input",
            maxLength: 255,
            value: parameters[side][dimension.key] ?? "",
            placeholder: text("wizard_any"),
            onChange: (event) => set(side, dimension.key, event.target.value)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
            lineNumber: 47,
            columnNumber: 17
          }
        )
      ] }, dimension.key, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
        lineNumber: 45,
        columnNumber: 44
      }))
    ] }, side, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
      lineNumber: 40,
      columnNumber: 56
    })),
    missing && /* @__PURE__ */ jsxDEV("p", { className: "fbm-problem", children: text("wizard_courses_required") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
      lineNumber: 58,
      columnNumber: 21
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CourseFilters.tsx",
    lineNumber: 39,
    columnNumber: 12
  });
}, "CourseFilters");
var CourseFilters_default = CourseFilters;
export {
  CourseFilters_default as default
};
//# sourceMappingURL=CourseFilters.dev.js.map
