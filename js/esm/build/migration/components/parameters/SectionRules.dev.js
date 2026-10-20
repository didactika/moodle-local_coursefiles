var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * How the sections of a source course are matched with the sections of the destination course: by position, by name, or pair by
 * pair.
 *
 * @module     local_filebank/migration/components/parameters/SectionRules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { MAX_SECTION_PAIRS } from "../../lib/parameters";
import ChoiceCards from "../ui/ChoiceCards";
import Icon from "../ui/Icon";
import IconButton from "../ui/IconButton";
const STRATEGIES = ["ordinal", "name", "mapping"];
const SectionRules = /* @__PURE__ */ __name(({ rules, onChange, text, missing }) => {
  const pairs = rules.sectionmap;
  const setPair = /* @__PURE__ */ __name((index, key, value) => onChange({
    ...rules,
    sectionmap: pairs.map((pair, position) => position === index ? { ...pair, [key]: value } : pair)
  }), "setPair");
  const invalid = /* @__PURE__ */ __name((value) => missing && !/^[1-9][0-9]*$/.test(value), "invalid");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-sections", children: [
    /* @__PURE__ */ jsxDEV(
      ChoiceCards,
      {
        label: text("rules_sections"),
        name: "fbm-section-strategy",
        value: rules.sectionstrategy,
        onChange: (sectionstrategy) => onChange({
          ...rules,
          sectionstrategy,
          sectionmap: sectionstrategy === "mapping" && pairs.length === 0 ? [{ source: "", target: "" }] : pairs
        }),
        choices: STRATEGIES.map((value) => ({
          value,
          label: text("rules_sections_" + value),
          hint: text("rules_sections_" + value + "_hint")
        }))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 37,
        columnNumber: 9
      }
    ),
    rules.sectionstrategy === "mapping" && /* @__PURE__ */ jsxDEV("div", { className: "fbm-pairs", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-pair fbm-pair-head", "aria-hidden": "true", children: [
        /* @__PURE__ */ jsxDEV("span", {}, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 54,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "form-label", children: text("rules_section_source") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 55,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", {}, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 56,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "form-label", children: text("rules_section_target") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 57,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", {}, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 58,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 53,
        columnNumber: 13
      }),
      pairs.map((pair, index) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-pair", children: [
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-row-number", children: index + 1 }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 61,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "number",
            min: 1,
            inputMode: "numeric",
            className: `form-control${invalid(pair.source) ? " is-invalid" : ""}`,
            value: pair.source,
            "aria-label": text("rules_section_source"),
            onChange: (event) => setPair(index, "source", event.target.value)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 62,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 71,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "number",
            min: 1,
            inputMode: "numeric",
            className: `form-control${invalid(pair.target) ? " is-invalid" : ""}`,
            value: pair.target,
            "aria-label": text("rules_section_target"),
            onChange: (event) => setPair(index, "target", event.target.value)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 72,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV(
          IconButton,
          {
            icon: "trash",
            label: text("rules_remove"),
            tone: "danger",
            onClick: () => onChange({ ...rules, sectionmap: pairs.filter((_, position) => position !== index) })
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 81,
            columnNumber: 17
          }
        )
      ] }, index, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 60,
        columnNumber: 41
      })),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-add", children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-outline-secondary",
          disabled: pairs.length >= MAX_SECTION_PAIRS,
          onClick: () => onChange({ ...rules, sectionmap: [...pairs, { source: "", target: "" }] }),
          children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
              lineNumber: 95,
              columnNumber: 21
            }),
            text("rules_section_add")
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 89,
          columnNumber: 17
        }
      ) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 88,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
      lineNumber: 52,
      columnNumber: 49
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
    lineNumber: 36,
    columnNumber: 12
  });
}, "SectionRules");
var SectionRules_default = SectionRules;
export {
  SectionRules_default as default
};
//# sourceMappingURL=SectionRules.dev.js.map
