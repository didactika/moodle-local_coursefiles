var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The window that starts a migration: the person chooses the parameters, sees what a migration with them would do, and only then
 * creates it. Nothing is stored until then, so parameters that find nothing to migrate are changed here and not in a new migration.
 *
 * @module     local_filebank/migration/components/wizard/NewMigrationModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { describeError } from "../../lib/errors";
import { keyOf, parametersFor, problemsOf } from "../../lib/parameters";
import { checkMigration, createMigration } from "../../services/api";
import CheckResult from "../parameters/CheckResult";
import ParametersEditor from "../parameters/ParametersEditor";
import Alert from "../ui/Alert";
import Icon from "../ui/Icon";
import Modal from "../ui/Modal";
const NewMigrationModal = /* @__PURE__ */ __name(({ open, options, initial, onClose, onCreated, text }) => {
  const [parameters, setParameters] = useState(null);
  const [step, setStep] = useState("setup");
  const [problems, setProblems] = useState([]);
  const [summary, setSummary] = useState(null);
  const [checking, setChecking] = useState(false);
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState("");
  useEffect(() => {
    if (!open || !options) {
      return;
    }
    setParameters(initial ?? (options.profiles[0] ? parametersFor(options.profiles[0]) : null));
    setStep("setup");
    setProblems([]);
    setSummary(null);
    setError("");
  }, [open, options, initial]);
  if (!options || !parameters) {
    return /* @__PURE__ */ jsxDEV(Modal, { open, size: "large", title: text("wizard_title"), onClose, closeLabel: text("close"), children: open && options && options.profiles.length === 0 ? /* @__PURE__ */ jsxDEV(Alert, { tone: "warning", children: text("wizard_no_profiles") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
      lineNumber: 59,
      columnNumber: 19
    }) : /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted", children: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
      lineNumber: 60,
      columnNumber: 19
    }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
      lineNumber: 57,
      columnNumber: 16
    });
  }
  const dimensions = options.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;
  const check = /* @__PURE__ */ __name(async () => {
    const found = problemsOf(parameters, dimensions);
    setProblems(found);
    if (found.length > 0) {
      return;
    }
    setStep("check");
    setChecking(true);
    setSummary(null);
    setError("");
    const asked = keyOf(parameters);
    try {
      const result = await checkMigration(parameters);
      if (asked === keyOf(parameters)) {
        setSummary(result);
      }
    } catch (problem) {
      setError(describeError(problem));
    } finally {
      setChecking(false);
    }
  }, "check");
  const create = /* @__PURE__ */ __name(async () => {
    setCreating(true);
    setError("");
    try {
      const created = await createMigration(parameters);
      onCreated(created.id);
    } catch (problem) {
      setError(describeError(problem));
      setCreating(false);
    }
  }, "create");
  const canCreate = summary !== null && (summary.files > 0 || summary.openissues > 0);
  return /* @__PURE__ */ jsxDEV(
    Modal,
    {
      open,
      size: "large",
      busy: creating,
      title: /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: "wand-magic-sparkles", gap: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 107,
          columnNumber: 18
        }),
        text("wizard_title")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
        lineNumber: 107,
        columnNumber: 16
      }),
      onClose,
      closeLabel: text("close"),
      footer: step === "setup" ? /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: onClose, children: text("cancel") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 112,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-primary", onClick: () => void check(), disabled: options.profiles.length === 0, children: [
          text("wizard_check"),
          /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 114,
            columnNumber: 43
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 113,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
        lineNumber: 111,
        columnNumber: 15
      }) : /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: () => setStep("setup"), disabled: creating, children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-left", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 119,
            columnNumber: 21
          }),
          text("wizard_change")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 118,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-primary", onClick: () => void create(), disabled: !canCreate || creating, children: [
          creating ? /* @__PURE__ */ jsxDEV(Icon, { name: "spinner", gap: true, spin: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 122,
            columnNumber: 33
          }) : /* @__PURE__ */ jsxDEV(Icon, { name: "check", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 122,
            columnNumber: 68
          }),
          text("wizard_create")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 121,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
        lineNumber: 117,
        columnNumber: 15
      }),
      children: [
        /* @__PURE__ */ jsxDEV("ol", { className: "fbm-steps", "aria-label": text("wizard_steps"), children: [
          /* @__PURE__ */ jsxDEV("li", { className: step === "setup" ? "is-current" : "is-done", children: [
            /* @__PURE__ */ jsxDEV("span", { children: "1" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
              lineNumber: 127,
              columnNumber: 73
            }),
            text("wizard_step_setup")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 127,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("li", { className: step === "check" ? "is-current" : "", children: [
            /* @__PURE__ */ jsxDEV("span", { children: "2" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
              lineNumber: 128,
              columnNumber: 66
            }),
            text("wizard_step_check")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
            lineNumber: 128,
            columnNumber: 13
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 126,
          columnNumber: 9
        }),
        step === "setup" ? /* @__PURE__ */ jsxDEV(ParametersEditor, { options, parameters, onChange: setParameters, text, problems }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 131,
          columnNumber: 15
        }) : /* @__PURE__ */ jsxDEV(CheckResult, { summary, loading: checking, error, text }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 132,
          columnNumber: 15
        }),
        step === "setup" && error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 133,
          columnNumber: 46
        }),
        step === "check" && summary !== null && !canCreate && /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-wizard-hint", children: text("wizard_change_hint") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
          lineNumber: 134,
          columnNumber: 64
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/wizard/NewMigrationModal.tsx",
      lineNumber: 103,
      columnNumber: 12
    }
  );
}, "NewMigrationModal");
var NewMigrationModal_default = NewMigrationModal;
export {
  NewMigrationModal_default as default
};
//# sourceMappingURL=NewMigrationModal.dev.js.map
