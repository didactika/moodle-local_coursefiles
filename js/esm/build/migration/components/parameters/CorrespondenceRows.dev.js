var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which folder of the source goes into which folder of the destination: one card for each correspondence, with the way each side
 * is recognised.
 *
 * @module     local_filebank/migration/components/parameters/CorrespondenceRows
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { MAX_CORRESPONDENCES, sameNameCorrespondence } from "../../lib/parameters";
import Icon from "../ui/Icon";
const FIELDS = ["name", "idnumber"];
const METHODS = ["equals", "pattern", "template"];
const Side = /* @__PURE__ */ __name(({ side, index, row, modules, onField, text, missing }) => {
  const key = /* @__PURE__ */ __name((name) => `${side}${name}`, "key");
  const id = /* @__PURE__ */ __name((name) => `fbm-c${index}-${side}-${name}`, "id");
  const value = row[key("value")];
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-side fbm-side-${side}`, children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-side-title", children: text(side === "source" ? "rules_source_folder" : "rules_target_folder") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 50,
      columnNumber: 9
    }),
    side === "source" && /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "fbm-label", htmlFor: id("type"), children: text("rules_kind") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 52,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("select", { id: id("type"), className: "fbm-select", value: row.sourcetype, onChange: (event) => onField("sourcetype", event.target.value), children: modules.map((module) => /* @__PURE__ */ jsxDEV("option", { value: module.value, children: module.label }, module.value, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 54,
        columnNumber: 42
      })) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 53,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 51,
      columnNumber: 31
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-field-row", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "fbm-label", htmlFor: id("field"), children: text("rules_field") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 59,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("select", { id: id("field"), className: "fbm-select", value: row[key("field")], onChange: (event) => onField(key("field"), event.target.value), children: FIELDS.map((field) => /* @__PURE__ */ jsxDEV("option", { value: field, children: text("rules_field_" + field) }, field, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 61,
          columnNumber: 44
        })) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 60,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 58,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "fbm-label", htmlFor: id("method"), children: text("rules_match") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 65,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("select", { id: id("method"), className: "fbm-select", value: row[key("method")], onChange: (event) => onField(key("method"), event.target.value), children: METHODS.map((method) => /* @__PURE__ */ jsxDEV("option", { value: method, children: text("rules_match_" + method) }, method, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 67,
          columnNumber: 46
        })) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 66,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 64,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 57,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "fbm-label", htmlFor: id("value"), children: text("rules_value") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 72,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          id: id("value"),
          type: "text",
          className: `fbm-input${missing && value.trim() === "" ? " is-invalid" : ""}`,
          maxLength: 2048,
          value,
          onChange: (event) => onField(key("value"), event.target.value)
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 73,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 71,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
    lineNumber: 49,
    columnNumber: 12
  });
}, "Side");
const CorrespondenceRows = /* @__PURE__ */ __name(({ rows, modules, onChange, text, missing }) => {
  const change = /* @__PURE__ */ __name((index, key, value) => onChange(
    rows.map((row, position) => position === index ? { ...row, [key]: value } : row)
  ), "change");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-rows", children: [
    rows.map((row, index) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-head", children: [
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-row-number", children: index + 1 }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 93,
          columnNumber: 17
        }),
        rows.length > 1 && /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: "fbm-iconbtn",
            onClick: () => onChange(rows.filter((_, position) => position !== index)),
            title: text("rules_remove"),
            children: [
              /* @__PURE__ */ jsxDEV(Icon, { name: "trash" }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
                lineNumber: 100,
                columnNumber: 21
              }),
              /* @__PURE__ */ jsxDEV("span", { className: "fbm-sr", children: text("rules_remove") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
                lineNumber: 100,
                columnNumber: 42
              })
            ]
          },
          void 0,
          true,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
            lineNumber: 94,
            columnNumber: 37
          }
        )
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 92,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-sides", children: [
        /* @__PURE__ */ jsxDEV(Side, { side: "source", index, row, modules, text, missing, onField: (key, value) => change(index, key, value) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 104,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-row-arrow", children: /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 105,
          columnNumber: 49
        }) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 105,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(Side, { side: "target", index, row, modules, text, missing, onField: (key, value) => change(index, key, value) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 106,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 103,
        columnNumber: 13
      })
    ] }, index, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 91,
      columnNumber: 35
    })),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-add", children: [
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "fbm-btn",
          disabled: rows.length >= MAX_CORRESPONDENCES,
          onClick: () => onChange([...rows, sameNameCorrespondence()]),
          children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 116,
              columnNumber: 17
            }),
            text("rules_add")
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 110,
          columnNumber: 13
        }
      ),
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: text("rules_limit", { n: MAX_CORRESPONDENCES }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 118,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 109,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
    lineNumber: 90,
    columnNumber: 12
  });
}, "CorrespondenceRows");
var CorrespondenceRows_default = CorrespondenceRows;
export {
  CorrespondenceRows_default as default
};
//# sourceMappingURL=CorrespondenceRows.dev.js.map
