var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Everything a person decides about a migration before it is worked out: the courses it goes between, which folders go where and
 * what to do when something does not match. The same editor starts a new migration and changes one that has not run.
 *
 * @module     local_filebank/migration/components/parameters/ParametersEditor
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { parametersFor } from "../../lib/parameters";
import Icon from "../ui/Icon";
import CorrespondenceRows from "./CorrespondenceRows";
import CourseFilters from "./CourseFilters";
import PolicyFields from "./PolicyFields";
import SectionRules from "./SectionRules";
const Block = /* @__PURE__ */ __name(({ icon, title, help, children }) => /* @__PURE__ */ jsxDEV("section", { className: "fbm-section", children: [
  /* @__PURE__ */ jsxDEV("header", { className: "fbm-section-head", children: [
    /* @__PURE__ */ jsxDEV("span", { className: "fbm-section-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: icon }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
      lineNumber: 48,
      columnNumber: 44
    }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
      lineNumber: 48,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { children: [
      /* @__PURE__ */ jsxDEV("h4", { className: "fbm-h4", children: title }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 50,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-section-help", children: help }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 51,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
      lineNumber: 49,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
    lineNumber: 47,
    columnNumber: 5
  }),
  children
] }, void 0, true, {
  fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
  lineNumber: 46,
  columnNumber: 62
}), "Block");
const ParametersEditor = /* @__PURE__ */ __name(({ options, parameters, onChange, text, lockProfile = false, problems }) => {
  const profile = options.profiles.find((item) => item.id === parameters.profileid);
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-editor", children: [
    /* @__PURE__ */ jsxDEV(Block, { icon: "layer-group", title: text("wizard_profile"), help: text("wizard_profile_help"), children: /* @__PURE__ */ jsxDEV(
      "select",
      {
        className: `fbm-select${problems.includes("profile") ? " is-invalid" : ""}`,
        "aria-label": text("wizard_profile"),
        value: parameters.profileid,
        disabled: lockProfile,
        onChange: (event) => {
          const next = options.profiles.find((item) => item.id === Number(event.target.value));
          if (next) {
            onChange(parametersFor(next));
          }
        },
        children: [
          parameters.profileid <= 0 && /* @__PURE__ */ jsxDEV("option", { value: 0, children: text("wizard_profile_choose") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
            lineNumber: 74,
            columnNumber: 47
          }),
          options.profiles.map((item) => /* @__PURE__ */ jsxDEV("option", { value: item.id, children: item.name }, item.id, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
            lineNumber: 75,
            columnNumber: 49
          }))
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 62,
        columnNumber: 13
      }
    ) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
      lineNumber: 61,
      columnNumber: 9
    }),
    profile && /* @__PURE__ */ jsxDEV(Fragment, { children: [
      /* @__PURE__ */ jsxDEV(Block, { icon: "graduation-cap", title: text("wizard_courses"), help: text("wizard_courses_help"), children: /* @__PURE__ */ jsxDEV(
        CourseFilters,
        {
          dimensions: profile.dimensions,
          parameters,
          onChange,
          text,
          missing: problems.includes("courses")
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
          lineNumber: 80,
          columnNumber: 17
        }
      ) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 79,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(Block, { icon: "folder-tree", title: text("wizard_folders"), help: text("wizard_folders_help"), children: /* @__PURE__ */ jsxDEV(
        CorrespondenceRows,
        {
          rows: parameters.rules.correspondences,
          modules: options.modules,
          onChange: (correspondences) => onChange({ ...parameters, rules: { ...parameters.rules, correspondences } }),
          text,
          missing: problems.includes("correspondence")
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
          lineNumber: 89,
          columnNumber: 17
        }
      ) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 88,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(Block, { icon: "list-ol", title: text("wizard_sections"), help: text("wizard_sections_help"), children: /* @__PURE__ */ jsxDEV(
        SectionRules,
        {
          rules: parameters.rules,
          onChange: (rules) => onChange({ ...parameters, rules }),
          text,
          missing: problems.includes("sections")
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
          lineNumber: 98,
          columnNumber: 17
        }
      ) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 97,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(Block, { icon: "sliders", title: text("wizard_policies"), help: text("wizard_policies_help"), children: /* @__PURE__ */ jsxDEV(PolicyFields, { rules: parameters.rules, onChange: (rules) => onChange({ ...parameters, rules }), text }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 106,
        columnNumber: 17
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 105,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
      lineNumber: 78,
      columnNumber: 21
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
    lineNumber: 60,
    columnNumber: 12
  });
}, "ParametersEditor");
var ParametersEditor_default = ParametersEditor;
export {
  ParametersEditor_default as default
};
//# sourceMappingURL=ParametersEditor.dev.js.map
