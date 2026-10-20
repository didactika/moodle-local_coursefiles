var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A choice between a few things that need to be explained: each one is a card with its name and what it does, and the chosen one is
 * marked. It is a group of real radio buttons, so the keyboard and assistive technology work as they do in a form.
 *
 * @module     local_filebank/migration/components/ui/ChoiceCards
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const ChoiceCards = /* @__PURE__ */ __name(({ label, name, value, choices, onChange }) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-choices", role: "radiogroup", "aria-label": label, children: choices.map((choice) => /* @__PURE__ */ jsxDEV("label", { className: `fbm-choice${choice.value === value ? " is-on" : ""}`, children: [
  /* @__PURE__ */ jsxDEV(
    "input",
    {
      type: "radio",
      name,
      value: choice.value,
      checked: choice.value === value,
      onChange: () => onChange(choice.value)
    },
    void 0,
    false,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
      lineNumber: 31,
      columnNumber: 9
    }
  ),
  /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-dot", "aria-hidden": "true" }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
    lineNumber: 38,
    columnNumber: 9
  }),
  /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-text", children: [
    /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-title", children: choice.label }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
      lineNumber: 40,
      columnNumber: 13
    }),
    choice.hint && /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-hint", children: choice.hint }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
      lineNumber: 41,
      columnNumber: 29
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
    lineNumber: 39,
    columnNumber: 9
  })
] }, choice.value, true, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
  lineNumber: 30,
  columnNumber: 30
})) }, void 0, false, {
  fileName: "public/local/filebank/js/esm/src/migration/components/ui/ChoiceCards.tsx",
  lineNumber: 29,
  columnNumber: 84
}), "ChoiceCards");
var ChoiceCards_default = ChoiceCards;
export {
  ChoiceCards_default as default
};
//# sourceMappingURL=ChoiceCards.dev.js.map
