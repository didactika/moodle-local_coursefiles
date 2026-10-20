var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One step of what a person decides about a migration before it is worked out: the profile, the courses it goes between, which
 * folders go where, how sections match and what to do when something does not match. The same steps start a new migration and
 * change one that has not run.
 *
 * @module     local_filebank/migration/components/parameters/ParametersEditor
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { parametersFor, profileRules } from "../../lib/parameters";
import CorrespondenceRows from "./CorrespondenceRows";
import CourseFilters from "./CourseFilters";
import PolicyFields from "./PolicyFields";
import SectionRules from "./SectionRules";
const ParametersEditor = /* @__PURE__ */ __name(({ step, options, parameters, onChange, text, lockProfile = false, problems }) => {
  const profile = options.profiles.find((item) => item.id === parameters.profileid);
  if (step === "profile") {
    return /* @__PURE__ */ jsxDEV(
      "select",
      {
        className: `fbm-select fbm-select-wide${problems.includes("profile") ? " is-invalid" : ""}`,
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
            lineNumber: 49,
            columnNumber: 43
          }),
          options.profiles.map((item) => /* @__PURE__ */ jsxDEV("option", { value: item.id, children: item.name }, item.id, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
            lineNumber: 50,
            columnNumber: 45
          }))
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 37,
        columnNumber: 16
      }
    );
  }
  if (!profile) {
    return null;
  }
  if (step === "courses") {
    return /* @__PURE__ */ jsxDEV(
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
        lineNumber: 57,
        columnNumber: 16
      }
    );
  }
  if (step === "folders") {
    return /* @__PURE__ */ jsxDEV(
      CorrespondenceRows,
      {
        rows: parameters.rules.correspondences,
        profileRows: profileRules(profile).correspondences,
        modules: options.modules,
        onChange: (correspondences) => onChange({ ...parameters, rules: { ...parameters.rules, correspondences } }),
        text,
        missing: problems.includes("correspondence")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 66,
        columnNumber: 16
      }
    );
  }
  if (step === "sections") {
    return /* @__PURE__ */ jsxDEV(
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
        lineNumber: 76,
        columnNumber: 16
      }
    );
  }
  return /* @__PURE__ */ jsxDEV(PolicyFields, { rules: parameters.rules, onChange: (rules) => onChange({ ...parameters, rules }), text }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
    lineNumber: 83,
    columnNumber: 12
  });
}, "ParametersEditor");
var ParametersEditor_default = ParametersEditor;
export {
  ParametersEditor_default as default
};
//# sourceMappingURL=ParametersEditor.dev.js.map
