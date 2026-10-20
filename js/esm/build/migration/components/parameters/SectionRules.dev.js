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
import Chips from "../ui/Chips";
import Icon from "../ui/Icon";
const STRATEGIES = ["ordinal", "name", "mapping"];
const SectionRules = /* @__PURE__ */ __name(({ rules, onChange, text, missing }) => {
  const pairs = rules.sectionmap;
  const setPair = /* @__PURE__ */ __name((index, key, value) => onChange({
    ...rules,
    sectionmap: pairs.map((pair, position) => position === index ? { ...pair, [key]: value } : pair)
  }), "setPair");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-sections", children: [
    /* @__PURE__ */ jsxDEV(
      Chips,
      {
        label: text("rules_sections"),
        value: rules.sectionstrategy,
        onChange: (sectionstrategy) => onChange({
          ...rules,
          sectionstrategy,
          sectionmap: sectionstrategy === "mapping" && pairs.length === 0 ? [{ source: "", target: "" }] : pairs
        }),
        chips: STRATEGIES.map((value) => ({ value, label: text("rules_sections_" + value) }))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 35,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-hint", children: text("rules_sections_" + rules.sectionstrategy + "_hint") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
      lineNumber: 45,
      columnNumber: 9
    }),
    rules.sectionstrategy === "mapping" && /* @__PURE__ */ jsxDEV("div", { className: "fbm-pairs", children: [
      pairs.map((pair, index) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-pair", children: [
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "text",
            inputMode: "numeric",
            className: `fbm-input${missing && !/^[1-9][0-9]*$/.test(pair.source) ? " is-invalid" : ""}`,
            value: pair.source,
            "aria-label": text("rules_section_source"),
            placeholder: text("rules_section_source"),
            onChange: (event) => setPair(index, "source", event.target.value)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 48,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 57,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "input",
          {
            type: "text",
            inputMode: "numeric",
            className: `fbm-input${missing && !/^[1-9][0-9]*$/.test(pair.target) ? " is-invalid" : ""}`,
            value: pair.target,
            "aria-label": text("rules_section_target"),
            placeholder: text("rules_section_target"),
            onChange: (event) => setPair(index, "target", event.target.value)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 58,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: "fbm-iconbtn",
            title: text("rules_remove"),
            onClick: () => onChange({ ...rules, sectionmap: pairs.filter((_, position) => position !== index) }),
            children: [
              /* @__PURE__ */ jsxDEV(Icon, { name: "trash" }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
                lineNumber: 73,
                columnNumber: 21
              }),
              /* @__PURE__ */ jsxDEV("span", { className: "fbm-sr", children: text("rules_remove") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
                lineNumber: 73,
                columnNumber: 42
              })
            ]
          },
          void 0,
          true,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
            lineNumber: 67,
            columnNumber: 17
          }
        )
      ] }, index, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
        lineNumber: 47,
        columnNumber: 41
      })),
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "fbm-btn",
          disabled: pairs.length >= MAX_SECTION_PAIRS,
          onClick: () => onChange({ ...rules, sectionmap: [...pairs, { source: "", target: "" }] }),
          children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
              lineNumber: 82,
              columnNumber: 17
            }),
            text("rules_section_add")
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
          lineNumber: 76,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
      lineNumber: 46,
      columnNumber: 49
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/SectionRules.tsx",
    lineNumber: 34,
    columnNumber: 12
  });
}, "SectionRules");
var SectionRules_default = SectionRules;
export {
  SectionRules_default as default
};
//# sourceMappingURL=SectionRules.dev.js.map
