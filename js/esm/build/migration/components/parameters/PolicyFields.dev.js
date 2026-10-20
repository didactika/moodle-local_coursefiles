var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What to do when something does not match. Each choice is a card that says what it does; none of them stops a migration. What
 * happens with a file that is already different in the destination is decided in the last step, where it is known how many there
 * are.
 *
 * @module     local_filebank/migration/components/parameters/PolicyFields
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ChoiceCards from "../ui/ChoiceCards";
const POLICIES = [
  { key: "missingtarget", values: ["create", "skip"] },
  { key: "duplicatepolicy", values: ["skip-and-report", "keep"] }
];
const PolicyFields = /* @__PURE__ */ __name(({ rules, onChange, text }) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-policies", children: POLICIES.map((policy) => {
  const value = policy.values.includes(rules[policy.key]) ? rules[policy.key] : policy.values[0];
  const name = /* @__PURE__ */ __name((item) => "policy_" + policy.key + "_" + item.split("-").join("_"), "name");
  return /* @__PURE__ */ jsxDEV("fieldset", { className: "fbm-policy", children: [
    /* @__PURE__ */ jsxDEV("legend", { className: "fbm-policy-title", children: text("policy_" + policy.key) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
      lineNumber: 40,
      columnNumber: 13
    }),
    /* @__PURE__ */ jsxDEV(
      ChoiceCards,
      {
        label: text("policy_" + policy.key),
        name: `fbm-policy-${policy.key}`,
        value,
        onChange: (next) => onChange({ ...rules, [policy.key]: next }),
        choices: policy.values.map((item) => ({ value: item, label: text(name(item)), hint: text(name(item) + "_hint") }))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/PolicyFields.tsx",
        lineNumber: 41,
        columnNumber: 13
      }
    )
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
