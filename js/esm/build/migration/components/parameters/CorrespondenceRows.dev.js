var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which folder of the source goes into which folder of the destination: one card for each correspondence, with the way each side
 * is recognised. There is none to start with; the person adds the ones the migration needs.
 *
 * @module     local_filebank/migration/components/parameters/CorrespondenceRows
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { emptyCorrespondence, MAX_CORRESPONDENCES } from "../../lib/parameters";
import Icon from "../ui/Icon";
import IconButton from "../ui/IconButton";
const FIELDS = ["name", "idnumber"];
const METHODS = ["equals", "pattern", "template"];
const Side = /* @__PURE__ */ __name(({ side, index, row, modules, onField, text, missing }) => {
  const key = /* @__PURE__ */ __name((name) => `${side}${name}`, "key");
  const id = /* @__PURE__ */ __name((name) => `fbm-c${index}-${side}-${name}`, "id");
  const value = row[key("value")];
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-side fbm-side-${side}`, children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-side-title", children: text(side === "source" ? "rules_source_folder" : "rules_target_folder") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 54,
      columnNumber: 9
    }),
    side === "source" && /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "form-label", htmlFor: id("type"), children: text("rules_kind") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 56,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("select", { id: id("type"), className: "form-select", value: row.sourcetype, onChange: (event) => onField("sourcetype", event.target.value), children: modules.map((module) => /* @__PURE__ */ jsxDEV("option", { value: module.value, children: module.label }, module.value, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 58,
        columnNumber: 42
      })) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 57,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 55,
      columnNumber: 31
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-field-row", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "form-label", htmlFor: id("field"), children: text("rules_field") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 63,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "select",
          {
            id: id("field"),
            className: "form-select",
            value: row[key("field")],
            onChange: (event) => onField(key("field"), event.target.value),
            children: FIELDS.map((field) => /* @__PURE__ */ jsxDEV("option", { value: field, children: text("rules_field_" + field) }, field, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 70,
              columnNumber: 44
            }))
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
            lineNumber: 64,
            columnNumber: 17
          }
        )
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 62,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "form-label", htmlFor: id("method"), children: text("rules_match") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 74,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "select",
          {
            id: id("method"),
            className: "form-select",
            value: row[key("method")],
            onChange: (event) => onField(key("method"), event.target.value),
            children: METHODS.map((method) => /* @__PURE__ */ jsxDEV("option", { value: method, children: text("rules_match_" + method) }, method, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 81,
              columnNumber: 46
            }))
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
            lineNumber: 75,
            columnNumber: 17
          }
        )
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 73,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 61,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-field", children: [
      /* @__PURE__ */ jsxDEV("label", { className: "form-label", htmlFor: id("value"), children: text("rules_value") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 86,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          id: id("value"),
          type: "text",
          className: `form-control${missing && value.trim() === "" ? " is-invalid" : ""}`,
          maxLength: 2048,
          value,
          onChange: (event) => onField(key("value"), event.target.value)
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 87,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 85,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
    lineNumber: 53,
    columnNumber: 12
  });
}, "Side");
const CorrespondenceRows = /* @__PURE__ */ __name(({ rows, previous, onReuse, modules, onChange, text, missing }) => {
  const change = /* @__PURE__ */ __name((index, key, value) => onChange(
    rows.map((row, position) => position === index ? { ...row, [key]: value } : row)
  ), "change");
  const room = rows.length < MAX_CORRESPONDENCES;
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-rows", children: [
    rows.length === 0 && /* @__PURE__ */ jsxDEV("div", { className: `fbm-rows-empty${missing ? " has-problem" : ""}`, "data-region": "no-correspondences", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-empty-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: "folder-open" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 107,
        columnNumber: 45
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 107,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-empty-text", children: text("rules_empty") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 108,
        columnNumber: 13
      }),
      missing && /* @__PURE__ */ jsxDEV("p", { className: "fbm-problem", children: text("rules_required") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 109,
        columnNumber: 25
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 106,
      columnNumber: 31
    }),
    rows.map((row, index) => /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-head", children: [
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-row-number", children: index + 1 }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 113,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          IconButton,
          {
            icon: "trash",
            label: text("rules_remove"),
            tone: "danger",
            onClick: () => onChange(rows.filter((_, position) => position !== index))
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
            lineNumber: 114,
            columnNumber: 17
          }
        )
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 112,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-sides", children: [
        /* @__PURE__ */ jsxDEV(Side, { side: "source", index, row, modules, text, missing, onField: (key, value) => change(index, key, value) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 122,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-row-arrow", children: /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 123,
          columnNumber: 49
        }) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 123,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(Side, { side: "target", index, row, modules, text, missing, onField: (key, value) => change(index, key, value) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 124,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 121,
        columnNumber: 13
      })
    ] }, index, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 111,
      columnNumber: 35
    })),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-row-add", children: [
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-primary",
          disabled: !room,
          onClick: () => onChange([...rows, emptyCorrespondence()]),
          "data-action": "add-correspondence",
          children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 135,
              columnNumber: 17
            }),
            text("rules_add")
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 128,
          columnNumber: 13
        }
      ),
      previous.length > 0 && rows.length === 0 && /* @__PURE__ */ jsxDEV(
        "select",
        {
          className: "form-select fbm-select-auto",
          "aria-label": text("rules_reuse"),
          value: "",
          onChange: (event) => onReuse(Number(event.target.value)),
          "data-action": "reuse-correspondences",
          children: [
            /* @__PURE__ */ jsxDEV("option", { value: "", disabled: true, children: text("rules_reuse") }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 144,
              columnNumber: 17
            }),
            previous.map((item) => /* @__PURE__ */ jsxDEV("option", { value: item.id, children: item.label }, item.id, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
              lineNumber: 145,
              columnNumber: 41
            }))
          ]
        },
        void 0,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
          lineNumber: 137,
          columnNumber: 58
        }
      ),
      /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: text("rules_limit", { n: MAX_CORRESPONDENCES }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
        lineNumber: 147,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
      lineNumber: 127,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CorrespondenceRows.tsx",
    lineNumber: 105,
    columnNumber: 12
  });
}, "CorrespondenceRows");
var CorrespondenceRows_default = CorrespondenceRows;
export {
  CorrespondenceRows_default as default
};
//# sourceMappingURL=CorrespondenceRows.dev.js.map
