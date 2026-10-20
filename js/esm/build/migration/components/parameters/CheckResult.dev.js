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
import EmptyState from "../ui/EmptyState";
import Icon from "../ui/Icon";
import Skeleton from "../ui/Skeleton";
import StatTile from "../ui/StatTile";
const CheckResult = /* @__PURE__ */ __name(({ summary, loading, error, text }) => {
  if (error !== "") {
    return /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 29,
      columnNumber: 16
    });
  }
  if (loading || !summary) {
    return /* @__PURE__ */ jsxDEV(Skeleton, { rows: 3, label: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 32,
      columnNumber: 16
    });
  }
  if (summary.files === 0) {
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
        lineNumber: 35,
        columnNumber: 16
      }
    );
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-check-result", "data-region": "check-result", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-tiles", children: [
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_tocopy"), value: summary.tocopy, tone: "success", icon: "copy" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 44,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_tooverwrite"), value: summary.tooverwrite, tone: "warning", icon: "pen-to-square" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 45,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_existing"), value: summary.existing, tone: "neutral", icon: "equals" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 46,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(StatTile, { label: text("summary_conflicts"), value: summary.conflicts + summary.openissues, tone: "info", icon: "hand-pointer" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 47,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 43,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-check-scope", children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "folder-tree", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 50,
        columnNumber: 13
      }),
      text("check_scope", {
        folders: summary.folders,
        from: summary.sourcecourses,
        to: summary.targetcourses
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 49,
      columnNumber: 9
    }),
    !summary.haswork && /* @__PURE__ */ jsxDEV(Alert, { tone: "info", children: text("check_no_work") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 54,
      columnNumber: 30
    }),
    summary.conflicts + summary.openissues > 0 && /* @__PURE__ */ jsxDEV(Alert, { tone: "info", children: text("check_decisions_later") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 55,
      columnNumber: 56
    }),
    summary.notes.length > 0 && /* @__PURE__ */ jsxDEV("ul", { className: "fbm-notes", children: summary.notes.map((note) => /* @__PURE__ */ jsxDEV("li", { children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "circle-info", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
        lineNumber: 57,
        columnNumber: 62
      }),
      note.label
    ] }, note.kind, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 57,
      columnNumber: 42
    })) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
      lineNumber: 56,
      columnNumber: 38
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/CheckResult.tsx",
    lineNumber: 42,
    columnNumber: 12
  });
}, "CheckResult");
var CheckResult_default = CheckResult;
export {
  CheckResult_default as default
};
//# sourceMappingURL=CheckResult.dev.js.map
