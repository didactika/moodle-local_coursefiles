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
import { parametersFor, parametersOf } from "../../lib/parameters";
import { getMigration } from "../../services/api";
import CorrespondenceRows from "./CorrespondenceRows";
import CourseFilters from "./CourseFilters";
import PolicyFields from "./PolicyFields";
import ProfilePicker from "./ProfilePicker";
import SectionRules from "./SectionRules";
const ParametersEditor = /* @__PURE__ */ __name(({ step, options, parameters, onChange, onOptions, text, lockProfile = false, problems }) => {
  const profile = options.profiles.find((item) => item.id === parameters.profileid);
  if (step === "profile") {
    return /* @__PURE__ */ jsxDEV(
      ProfilePicker,
      {
        profiles: options.profiles,
        chosen: parameters.profileid,
        locked: lockProfile,
        invalid: problems.includes("profile"),
        onPick: (next) => {
          onChange(parametersFor(next));
          onOptions("", next.id);
        },
        onSearch: (query) => onOptions(query, parameters.profileid),
        text
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 41,
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
        lineNumber: 58,
        columnNumber: 16
      }
    );
  }
  if (step === "folders") {
    const reuse = /* @__PURE__ */ __name(async (id) => {
      const earlier = parametersOf(await getMigration(id));
      onChange({ ...parameters, rules: { ...parameters.rules, correspondences: earlier.rules.correspondences } });
    }, "reuse");
    return /* @__PURE__ */ jsxDEV(
      CorrespondenceRows,
      {
        rows: parameters.rules.correspondences,
        previous: options.previous,
        onReuse: (id) => void reuse(id).catch(() => void 0),
        modules: options.modules,
        onChange: (correspondences) => onChange({ ...parameters, rules: { ...parameters.rules, correspondences } }),
        text,
        missing: problems.includes("correspondence")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
        lineNumber: 71,
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
        lineNumber: 82,
        columnNumber: 16
      }
    );
  }
  return /* @__PURE__ */ jsxDEV(PolicyFields, { rules: parameters.rules, onChange: (rules) => onChange({ ...parameters, rules }), text }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ParametersEditor.tsx",
    lineNumber: 89,
    columnNumber: 12
  });
}, "ParametersEditor");
var ParametersEditor_default = ParametersEditor;
export {
  ParametersEditor_default as default
};
//# sourceMappingURL=ParametersEditor.dev.js.map
