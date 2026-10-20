var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The page that starts a migration, or changes the parameters of one that has not run: the steps are listed on the left, the open
 * one is on the right, and what a migration with the chosen parameters would do is counted in the last one before anything is
 * stored. It is a page and not a window, so nothing that was typed is lost by clicking away; leaving it asks first.
 *
 * @module     local_filebank/migration/components/wizard/MigrationWizard
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { describeError } from "../../lib/errors";
import {
  keyOf,
  parametersFor,
  parametersOf,
  STEP_KEYS,
  stepProblems
} from "../../lib/parameters";
import { addToast } from "../../lib/toast";
import { checkMigration, createMigration, getMigration, reconfigureMigration } from "../../services/api";
import CheckResult from "../parameters/CheckResult";
import ParametersEditor from "../parameters/ParametersEditor";
import Alert from "../ui/Alert";
import ConfirmModal from "../ui/ConfirmModal";
import Icon from "../ui/Icon";
import PageHeader from "../ui/PageHeader";
import Skeleton from "../ui/Skeleton";
import Stepper from "./Stepper";
const STEP_ICONS = {
  profile: "layer-group",
  courses: "graduation-cap",
  folders: "folder-tree",
  sections: "list-ol",
  policies: "sliders",
  check: "clipboard-check"
};
const MigrationWizard = /* @__PURE__ */ __name(({ mode, options, migrationId, onClose, onDone, onOptions, text }) => {
  const [parameters, setParameters] = useState(null);
  const [source, setSource] = useState(null);
  const [step, setStep] = useState(0);
  const [reached, setReached] = useState(mode === "edit" ? STEP_KEYS.length - 1 : 0);
  const [problems, setProblems] = useState([]);
  const [summary, setSummary] = useState(null);
  const [checking, setChecking] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [leaving, setLeaving] = useState(false);
  const initial = useRef("");
  const root = useRef(null);
  const ready = options !== null && parameters !== null;
  const steps = STEP_KEYS;
  useEffect(() => {
    if (!options) {
      return void 0;
    }
    let current = true;
    const start = /* @__PURE__ */ __name((found, migration) => {
      if (!current) {
        return;
      }
      const base = found ?? (options.profiles[0] ? parametersFor(options.profiles[0]) : null);
      initial.current = base ? keyOf(base) : "";
      setParameters(base);
      setSource(migration);
    }, "start");
    if (migrationId > 0) {
      getMigration(migrationId).then((migration) => start(parametersOf(migration), migration)).catch((problem) => {
        if (current) {
          setError(describeError(problem));
        }
      });
    } else {
      start(null, null);
    }
    return () => {
      current = false;
    };
  }, [options, migrationId]);
  useEffect(() => {
    if (ready && window.matchMedia("(min-width: 62rem) and (min-height: 36rem)").matches) {
      root.current?.scrollIntoView({ block: "start" });
    }
  }, [ready]);
  const chosen = parameters?.profileid ?? 0;
  useEffect(() => {
    if (chosen > 0) {
      onOptions("", chosen);
    }
  }, [chosen]);
  const dirty = parameters !== null && keyOf(parameters) !== initial.current;
  useEffect(() => {
    if (!dirty) {
      return void 0;
    }
    const warn = /* @__PURE__ */ __name((event) => {
      event.preventDefault();
    }, "warn");
    window.addEventListener("beforeunload", warn);
    return () => window.removeEventListener("beforeunload", warn);
  }, [dirty]);
  const heading = mode === "edit" ? text("edit_title") : text("wizard_title");
  const back = /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-link btn-sm p-0 fbm-back", onClick: () => dirty ? setLeaving(true) : onClose(), children: [
    /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-left", gap: true }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
      lineNumber: 130,
      columnNumber: 9
    }),
    text(mode === "edit" ? "back_to_migration" : "back_to_list")
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
    lineNumber: 129,
    columnNumber: 18
  });
  if (!options || !parameters) {
    return /* @__PURE__ */ jsxDEV("div", { className: "fbm-wizard", children: [
      /* @__PURE__ */ jsxDEV(PageHeader, { title: heading, back }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 135,
        columnNumber: 13
      }),
      error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 136,
        columnNumber: 30
      }),
      options && options.profiles.length === 0 && /* @__PURE__ */ jsxDEV(Alert, { tone: "warning", children: text("wizard_no_profiles") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 137,
        columnNumber: 58
      }),
      error === "" && (!options || options.profiles.length > 0) && /* @__PURE__ */ jsxDEV(Skeleton, { rows: 5, label: text("loading") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 138,
        columnNumber: 75
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
      lineNumber: 134,
      columnNumber: 16
    });
  }
  const dimensions = options.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;
  const key = steps[step];
  const last = step === steps.length - 1;
  const change = /* @__PURE__ */ __name((next2) => {
    setParameters(next2);
    setSummary(null);
  }, "change");
  const runCheck = /* @__PURE__ */ __name(async (asked = parameters, keepResult = false) => {
    setChecking(true);
    if (!keepResult) {
      setSummary(null);
    }
    setError("");
    try {
      const result = await checkMigration(asked);
      setSummary(result);
    } catch (problem) {
      setError(describeError(problem));
    } finally {
      setChecking(false);
    }
  }, "runCheck");
  const go = /* @__PURE__ */ __name((index) => {
    setStep(index);
    setProblems([]);
    setError("");
    setReached(Math.max(reached, index));
    if (steps[index] === "check") {
      void runCheck();
    }
  }, "go");
  const next = /* @__PURE__ */ __name(() => {
    const found = stepProblems(key, parameters, dimensions);
    setProblems(found);
    if (found.length === 0) {
      go(step + 1);
    }
  }, "next");
  const save = /* @__PURE__ */ __name(async () => {
    setSaving(true);
    setError("");
    try {
      if (mode === "edit" && source) {
        await reconfigureMigration(source.id, source.digest, parameters);
        void addToast(text("edit_saved"));
        initial.current = keyOf(parameters);
        onDone(source.id);
      } else {
        const created = await createMigration(parameters);
        initial.current = keyOf(parameters);
        onDone(created.id);
      }
    } catch (problem) {
      setError(describeError(problem));
      setSaving(false);
    }
  }, "save");
  const canSave = summary !== null && summary.haswork && !checking;
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-wizard", "data-region": "migration-wizard", "data-mode": mode, ref: root, children: [
    /* @__PURE__ */ jsxDEV(
      PageHeader,
      {
        title: mode === "edit" ? text("edit_title_n", { n: source?.id ?? migrationId }) : heading,
        subtitle: text(mode === "edit" ? "edit_hint" : "wizard_subtitle"),
        back
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 210,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-wizard-body", children: [
      /* @__PURE__ */ jsxDEV(Stepper, { steps, current: step, reached, onGo: go, text }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 216,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("section", { className: "fbm-wizard-panel", "aria-live": "polite", children: [
        /* @__PURE__ */ jsxDEV("header", { className: "fbm-wizard-head", children: [
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-wizard-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: STEP_ICONS[key] }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 219,
            columnNumber: 55
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 219,
            columnNumber: 21
          }),
          /* @__PURE__ */ jsxDEV("div", { children: [
            /* @__PURE__ */ jsxDEV("h3", { className: "h3", children: text("step_" + key) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 221,
              columnNumber: 25
            }),
            /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-wizard-help", children: text("step_" + key + "_help") }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 222,
              columnNumber: 25
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 220,
            columnNumber: 21
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
          lineNumber: 218,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-wizard-scroll", children: [
          error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 226,
            columnNumber: 34
          }),
          key === "check" ? /* @__PURE__ */ jsxDEV(
            CheckResult,
            {
              summary,
              loading: checking,
              error: "",
              text,
              policy: parameters.rules.conflictpolicy,
              onPolicy: (conflictpolicy) => {
                const next2 = { ...parameters, rules: { ...parameters.rules, conflictpolicy } };
                setParameters(next2);
                void runCheck(next2, true);
              }
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 228,
              columnNumber: 23
            }
          ) : /* @__PURE__ */ jsxDEV(
            ParametersEditor,
            {
              step: key,
              options,
              parameters,
              onChange: change,
              onOptions,
              text,
              lockProfile: mode === "edit",
              problems
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 240,
              columnNumber: 23
            }
          ),
          key === "check" && summary !== null && !canSave && /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-wizard-hint", children: text("wizard_change_hint") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 250,
            columnNumber: 69
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
          lineNumber: 225,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("footer", { className: "fbm-wizard-foot", children: [
          /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary", onClick: () => go(step - 1), disabled: step === 0 || saving, children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-left", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 254,
              columnNumber: 25
            }),
            text("wizard_previous")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 253,
            columnNumber: 21
          }),
          last ? /* @__PURE__ */ jsxDEV(
            "button",
            {
              type: "button",
              className: "btn btn-primary",
              onClick: () => void save(),
              disabled: !canSave || saving,
              "data-action": "save-migration",
              children: [
                saving ? /* @__PURE__ */ jsxDEV(Icon, { name: "spinner", gap: true, spin: true }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
                  lineNumber: 264,
                  columnNumber: 39
                }) : /* @__PURE__ */ jsxDEV(Icon, { name: "check", gap: true }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
                  lineNumber: 264,
                  columnNumber: 74
                }),
                text(mode === "edit" ? "edit_save" : "wizard_create")
              ]
            },
            void 0,
            true,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 257,
              columnNumber: 27
            }
          ) : /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", onClick: next, "data-action": "next-step", children: [
            text(steps[step + 1] === "check" ? "wizard_check" : "wizard_next"),
            /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
              lineNumber: 268,
              columnNumber: 97
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
            lineNumber: 267,
            columnNumber: 27
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
          lineNumber: 252,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 217,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
      lineNumber: 215,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV(
      ConfirmModal,
      {
        open: leaving,
        tone: "danger",
        icon: "triangle-exclamation",
        title: text("discard_title"),
        confirmLabel: text("discard"),
        cancelLabel: text("keep_editing"),
        onConfirm: () => {
          initial.current = keyOf(parameters);
          onClose();
        },
        onCancel: () => setLeaving(false),
        children: /* @__PURE__ */ jsxDEV("p", { children: text("discard_text") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
          lineNumber: 286,
          columnNumber: 13
        })
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
        lineNumber: 273,
        columnNumber: 9
      }
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/wizard/MigrationWizard.tsx",
    lineNumber: 209,
    columnNumber: 12
  });
}, "MigrationWizard");
var MigrationWizard_default = MigrationWizard;
export {
  MigrationWizard_default as default
};
//# sourceMappingURL=MigrationWizard.dev.js.map
