var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What a migration with the chosen parameters would do, counted before anything is stored. It never says that something failed:
 * what does not match is listed as information and what needs a decision is only counted.
 *
 * @module     local_filebank/migration/components/parameters/CheckResult
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Alert from "../ui/Alert";
import ChoiceCards from "../ui/ChoiceCards";
import EmptyState from "../ui/EmptyState";
import Icon from "../ui/Icon";
import Skeleton from "../ui/Skeleton";
import StatTile from "../ui/StatTile";
const CheckResult = /* @__PURE__ */ __name(({ summary, loading, error, text, policy, onPolicy }) => {
  if (error !== "") {
    return /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 33,
      columnNumber: 16
    });
  }
  if (!summary) {
    return /* @__PURE__ */ jsxDEV(Skeleton, { rows: 3, label: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 36,
      columnNumber: 16
    });
  }
  if (summary.files === 0 && !loading) {
    return /* @__PURE__ */ jsxDEV(
      EmptyState,
      {
        plain: true,
        icon: "folder-open",
        title: text("check_nothing_title"),
        text: text("check_nothing_text")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 39,
        columnNumber: 16
      }
    );
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-check-result", "data-region": "check-result", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-tiles", children: [
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_tocopy"), value: summary.tocopy, tone: "success", icon: "copy" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 48,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_tooverwrite"), value: summary.tooverwrite, tone: "warning", icon: "pen-to-square" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 49,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_existing"), value: summary.existing, tone: "neutral", icon: "equals" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 50,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_left"), value: summary.skipped, tone: "neutral", icon: "hand" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 51,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 47,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-check-scope", children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "folder-tree", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 54,
        columnNumber: 13
      }),
      text("check_scope", {
        folders: summary.folders,
        from: summary.sourcecourses,
        to: summary.targetcourses
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 53,
      columnNumber: 9
    }),
    summary.different > 0 && /* @__PURE__ */ jsxDEV("section", { className: "fbm-conflict", "data-region": "conflict-choice", children: [
      /* @__PURE__ */ jsxDEV("h4", { className: "h5", children: text("check_conflict_title", { n: summary.different }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 59,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-hint", children: text("check_conflict_help") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 60,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(
        ChoiceCards,
        {
          label: text("policy_conflictpolicy"),
          name: "fbm-conflict-policy",
          value: policy === "overwrite" ? "overwrite" : "skip",
          onChange: onPolicy,
          choices: ["skip", "overwrite"].map((value) => ({
            value,
            label: text("policy_conflictpolicy_" + value),
            hint: text("policy_conflictpolicy_" + value + "_hint")
          }))
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
          lineNumber: 61,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 58,
      columnNumber: 35
    }),
    !summary.haswork && /* @__PURE__ */ jsxDEV(Alert, { tone: "warning", children: text("check_no_work") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 73,
      columnNumber: 30
    }),
    summary.notes.length > 0 && /* @__PURE__ */ jsxDEV("ul", { className: "fbm-notes", children: summary.notes.map((note) => /* @__PURE__ */ jsxDEV("li", { children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "circle-info", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 75,
        columnNumber: 62
      }),
      note.label
    ] }, note.kind, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 75,
      columnNumber: 42
    })) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 74,
      columnNumber: 38
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
    lineNumber: 46,
    columnNumber: 12
  });
}, "CheckResult");
var CheckResult_default = CheckResult;
export {
  CheckResult_default as default
};
//# sourceMappingURL=CheckResult.dev.js.map
