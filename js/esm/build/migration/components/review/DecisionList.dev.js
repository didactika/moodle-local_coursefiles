var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What waits for a decision of one nature, a page at a time. Some of it can be chosen and settled together, one thing can be
 * settled on its own row, and a long list does not become a long page.
 *
 * @module     local_filebank/migration/components/review/DecisionList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { describeError } from "../../lib/errors";
import { getDecisions } from "../../services/api";
import Alert from "../ui/Alert";
import Check from "../ui/Check";
import Icon from "../ui/Icon";
import Pagination from "../ui/Pagination";
import Skeleton from "../ui/Skeleton";
const PAGE_SIZE = 10;
const common = /* @__PURE__ */ __name((lists) => {
  if (lists.length === 0) {
    return [];
  }
  return lists[0].filter((choice) => lists.every((list) => list.some((other) => other.value === choice.value)));
}, "common");
const DecisionList = /* @__PURE__ */ __name(({ migrationId, digest, nature, text, busy, onApply }) => {
  const [page, setPage] = useState(0);
  const [data, setData] = useState(null);
  const [error, setError] = useState("");
  const [selected, setSelected] = useState([]);
  const [choice, setChoice] = useState("");
  const [own, setOwn] = useState({});
  useEffect(() => {
    let current = true;
    void getDecisions(migrationId, nature.kind, nature.nature, page, PAGE_SIZE).then((read) => {
      if (current) {
        setData(read);
        setError("");
        setSelected([]);
        setOwn({});
      }
    }).catch((problem) => {
      if (current) {
        setError(describeError(problem));
      }
    });
    return () => {
      current = false;
    };
  }, [migrationId, digest, nature.kind, nature.nature, page]);
  useEffect(() => {
    if (data && data.items.length === 0 && page > 0) {
      setPage(page - 1);
    }
  }, [data, page]);
  if (error !== "") {
    return /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
      lineNumber: 83,
      columnNumber: 16
    });
  }
  if (!data) {
    return /* @__PURE__ */ jsxDEV(Skeleton, { rows: 3, label: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
      lineNumber: 86,
      columnNumber: 16
    });
  }
  const items = data.items;
  const chosen = items.filter((item) => selected.includes(item.id));
  const shared = common(chosen.map((item) => item.choices));
  const selectedChoice = shared.some((item) => item.value === choice) ? choice : shared[0]?.value ?? "";
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-decisions", "data-region": "decision-list", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-decisions-bar", children: [
      /* @__PURE__ */ jsxDEV(
        Check,
        {
          label: text("select_all_page"),
          checked: items.length > 0 && chosen.length === items.length,
          partial: chosen.length > 0,
          onChange: (checked) => setSelected(checked ? items.map((item) => item.id) : [])
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
          lineNumber: 96,
          columnNumber: 13
        }
      ),
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: text("selected_n", { n: chosen.length }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
        lineNumber: 102,
        columnNumber: 13
      }),
      chosen.length > 0 && /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV("select", { className: "fbm-select", "aria-label": text("decision_choice"), value: selectedChoice, onChange: (event) => setChoice(event.target.value), children: shared.map((item) => /* @__PURE__ */ jsxDEV("option", { value: item.value, children: item.label }, item.value, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
          lineNumber: 105,
          columnNumber: 43
        })) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
          lineNumber: 104,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: "fbm-btn fbm-btn-primary",
            disabled: busy || selectedChoice === "",
            onClick: () => onApply(chosen.map((item) => item.id), selectedChoice),
            children: [
              /* @__PURE__ */ jsxDEV(Icon, { name: "check", gap: true }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
                lineNumber: 113,
                columnNumber: 21
              }),
              text("apply_selected")
            ]
          },
          void 0,
          true,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
            lineNumber: 107,
            columnNumber: 17
          }
        )
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
        lineNumber: 103,
        columnNumber: 35
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
      lineNumber: 95,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("ul", { className: "fbm-decision-items", children: items.map((item) => {
      const value = own[item.id] ?? item.choices[0]?.value ?? "";
      return /* @__PURE__ */ jsxDEV("li", { className: "fbm-decision-item", children: [
        /* @__PURE__ */ jsxDEV(
          Check,
          {
            label: item.label,
            checked: selected.includes(item.id),
            onChange: (checked) => setSelected(checked ? [...selected, item.id] : selected.filter((id) => id !== item.id))
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
            lineNumber: 121,
            columnNumber: 21
          }
        ),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-decision-text", children: [
          /* @__PURE__ */ jsxDEV("strong", { children: item.label }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
            lineNumber: 127,
            columnNumber: 25
          }),
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: item.context }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
            lineNumber: 128,
            columnNumber: 25
          }),
          item.detail !== "" && /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted fbm-small", children: item.detail }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
            lineNumber: 129,
            columnNumber: 48
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
          lineNumber: 126,
          columnNumber: 21
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-decision-own", children: [
          item.choices.length > 1 && /* @__PURE__ */ jsxDEV(
            "select",
            {
              className: "fbm-select",
              "aria-label": text("decision_choice"),
              value,
              onChange: (event) => setOwn({ ...own, [item.id]: event.target.value }),
              children: item.choices.map((option) => /* @__PURE__ */ jsxDEV("option", { value: option.value, children: option.label }, option.value, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
                lineNumber: 138,
                columnNumber: 59
              }))
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
              lineNumber: 132,
              columnNumber: 53
            }
          ),
          /* @__PURE__ */ jsxDEV(
            "button",
            {
              type: "button",
              className: "fbm-btn fbm-btn-small",
              disabled: busy || value === "",
              onClick: () => onApply([item.id], value),
              title: item.choices.length === 1 ? item.choices[0].label : text("apply"),
              children: item.choices.length === 1 ? item.choices[0].label : text("apply")
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
              lineNumber: 140,
              columnNumber: 25
            }
          )
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
          lineNumber: 131,
          columnNumber: 21
        })
      ] }, item.id, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
        lineNumber: 120,
        columnNumber: 24
      });
    }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
      lineNumber: 117,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV(Pagination, { page, perpage: PAGE_SIZE, total: data.total, onPage: setPage, text }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
      lineNumber: 153,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionList.tsx",
    lineNumber: 94,
    columnNumber: 12
  });
}, "DecisionList");
var DecisionList_default = DecisionList;
export {
  DecisionList_default as default
};
//# sourceMappingURL=DecisionList.dev.js.map
