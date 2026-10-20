var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the person can do with the migration now, said in one place: start it, change its parameters because there is nothing to
 * write, work it out again because the profile changed, follow it while it runs, or read how it ended.
 *
 * @module     local_filebank/migration/components/review/ActionPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Alert from "../ui/Alert";
import Icon from "../ui/Icon";
import Progress from "../ui/Progress";
const ActionPanel = /* @__PURE__ */ __name(({ migration, text, busy, onStart, onStop, onEdit, onRework }) => {
  const { run, summary } = migration;
  if (run?.active) {
    return /* @__PURE__ */ jsxDEV("div", { className: "fbm-action is-running", "data-region": "run", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-action-body", children: [
        /* @__PURE__ */ jsxDEV("strong", { children: text("run_running", { done: run.done, total: run.total }) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 34,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(Progress, { value: run.done, max: Math.max(1, run.total), label: text("progress") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 35,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: text("run_running_hint") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 36,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 33,
        columnNumber: 13
      }),
      run.cancancel && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-danger", onClick: onStop, disabled: busy, children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: "stop", gap: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 39,
          columnNumber: 17
        }),
        text("run_stop")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 38,
        columnNumber: 31
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
      lineNumber: 32,
      columnNumber: 16
    });
  }
  if (run) {
    const tone = run.state === "completed" ? "success" : run.state === "cancelled" ? "neutral" : "warning";
    return /* @__PURE__ */ jsxDEV(Alert, { tone, children: [
      /* @__PURE__ */ jsxDEV("strong", { children: text("run_" + run.state) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 46,
        columnNumber: 13
      }),
      " ",
      /* @__PURE__ */ jsxDEV("span", { children: text("run_done", { done: run.done, total: run.total }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 47,
        columnNumber: 13
      }),
      run.state === "partial" && /* @__PURE__ */ jsxDEV("span", { children: [
        " ",
        text("run_partial_hint")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 48,
        columnNumber: 41
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
      lineNumber: 45,
      columnNumber: 16
    });
  }
  if (migration.outdated) {
    return /* @__PURE__ */ jsxDEV(
      Alert,
      {
        tone: "warning",
        actions: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-primary", onClick: onRework, disabled: busy, children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "rotate", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
            lineNumber: 55,
            columnNumber: 17
          }),
          text("outdated_rework")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 54,
          columnNumber: 22
        }),
        children: [
          /* @__PURE__ */ jsxDEV("strong", { children: text("outdated_title") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
            lineNumber: 58,
            columnNumber: 13
          }),
          " ",
          text("outdated_text")
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 52,
        columnNumber: 16
      }
    );
  }
  if (!summary.haswork) {
    return /* @__PURE__ */ jsxDEV(
      Alert,
      {
        tone: "info",
        actions: migration.editable && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-primary", onClick: onEdit, disabled: busy, children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "sliders", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
            lineNumber: 65,
            columnNumber: 17
          }),
          text("edit_parameters")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
          lineNumber: 64,
          columnNumber: 44
        }),
        children: [
          /* @__PURE__ */ jsxDEV("strong", { children: text("nothing_title") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
            lineNumber: 68,
            columnNumber: 13
          }),
          " ",
          text("nothing_text")
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 62,
        columnNumber: 16
      }
    );
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-action is-ready", "data-region": "start", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-action-body", children: [
      /* @__PURE__ */ jsxDEV("strong", { children: text("start_ready", { n: summary.tocopy + summary.tooverwrite }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 73,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: summary.conflicts + summary.openissues > 0 ? text("start_with_decisions", { n: summary.conflicts + summary.openissues }) : text("start_all_set") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 74,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
      lineNumber: 72,
      columnNumber: 9
    }),
    migration.canexecute ? /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-success fbm-btn-large", onClick: onStart, disabled: busy, "data-action": "start", children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "play", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
        lineNumber: 82,
        columnNumber: 17
      }),
      text("start")
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
      lineNumber: 81,
      columnNumber: 15
    }) : /* @__PURE__ */ jsxDEV("span", { className: "fbm-muted", children: text("start_no_permission") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
      lineNumber: 84,
      columnNumber: 15
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/ActionPanel.tsx",
    lineNumber: 71,
    columnNumber: 12
  });
}, "ActionPanel");
var ActionPanel_default = ActionPanel;
export {
  ActionPanel_default as default
};
//# sourceMappingURL=ActionPanel.dev.js.map
