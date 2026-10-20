var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. None of the choices stops a migration: what is not decided now is decided afterwards,
 * file by file or all at once, inside the same migration.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Chips from "../ui/Chips";
const POLICIES = [
  { key: "missingtarget", values: ["create", "skip"] },
  { key: "conflictpolicy", values: ["block", "overwrite"] },
  { key: "duplicatepolicy", values: ["skip-and-report", "keep"] }
];
const PolicyFields = /* @__PURE__ */ __name(({ rules, onChange, text }) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-policies", children: POLICIES.map((policy) => {
  const value = policy.values.includes(rules[policy.key]) ? rules[policy.key] : policy.values[0];
  const name = /* @__PURE__ */ __name((item) => "policy_" + policy.key + "_" + item.split("-").join("_"), "name");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-policy", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-label", children: text("policy_" + policy.key) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
      lineNumber: 40,
      columnNumber: 13
    }),
    /* @__PURE__ */ jsxDEV(
      Chips,
      {
        label: text("policy_" + policy.key),
        value,
        onChange: (next) => onChange({ ...rules, [policy.key]: next }),
        chips: policy.values.map((item) => ({ value: item, label: text(name(item)) }))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
        lineNumber: 41,
        columnNumber: 13
      }
    ),
    /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-hint", children: text(name(value) + "_hint") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
      lineNumber: 47,
      columnNumber: 13
    })
  ] }, policy.key, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
    lineNumber: 39,
    columnNumber: 16
  });
}) }, void 0, false, {
  fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
  lineNumber: 33,
  columnNumber: 70
}), "PolicyFields");
var PolicyFields_default = PolicyFields;
export {
  PolicyFields_default as default
};
//# sourceMappingURL=PolicyFields.dev.js.map
