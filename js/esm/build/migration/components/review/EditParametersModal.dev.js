var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The window that changes the parameters of a migration that has not run. The migration is worked out again where it is, so it
 * keeps its number, its date and what was already decided; no other migration is created.
 *
 * @module     local_filebank/migration/components/review/EditParametersModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { problemsOf } from "../../lib/parameters";
import ParametersEditor from "../parameters/ParametersEditor";
import Alert from "../ui/Alert";
import Icon from "../ui/Icon";
import Modal from "../ui/Modal";
const EditParametersModal = /* @__PURE__ */ __name(({ open, options, initial, text, busy, error, onSave, onClose }) => {
  const [parameters, setParameters] = useState(initial);
  const [problems, setProblems] = useState([]);
  useEffect(() => {
    if (open) {
      setParameters(initial);
      setProblems([]);
    }
  }, [open, initial]);
  const dimensions = options?.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;
  const save = /* @__PURE__ */ __name(() => {
    const found = problemsOf(parameters, dimensions);
    setProblems(found);
    if (found.length === 0) {
      onSave(parameters);
    }
  }, "save");
  return /* @__PURE__ */ jsxDEV(
    Modal,
    {
      open,
      size: "large",
      busy,
      title: /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: "sliders", gap: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 57,
          columnNumber: 18
        }),
        text("edit_title")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
        lineNumber: 57,
        columnNumber: 16
      }),
      onClose,
      closeLabel: text("close"),
      footer: /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: onClose, disabled: busy, children: text("cancel") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 61,
          columnNumber: 13
        }),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn fbm-btn-primary", onClick: save, disabled: busy || !options, children: [
          busy ? /* @__PURE__ */ jsxDEV(Icon, { name: "spinner", gap: true, spin: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
            lineNumber: 63,
            columnNumber: 25
          }) : /* @__PURE__ */ jsxDEV(Icon, { name: "check", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
            lineNumber: 63,
            columnNumber: 60
          }),
          text("edit_save")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 62,
          columnNumber: 13
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
        lineNumber: 60,
        columnNumber: 17
      }),
      children: [
        /* @__PURE__ */ jsxDEV(Alert, { tone: "info", children: text("edit_hint") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 67,
          columnNumber: 9
        }),
        error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 68,
          columnNumber: 26
        }),
        options ? /* @__PURE__ */ jsxDEV(ParametersEditor, { options, parameters, onChange: setParameters, text, lockProfile: true, problems }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 70,
          columnNumber: 15
        }) : /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted", children: text("loading") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
          lineNumber: 71,
          columnNumber: 15
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/EditParametersModal.tsx",
      lineNumber: 53,
      columnNumber: 12
    }
  );
}, "EditParametersModal");
var EditParametersModal_default = EditParametersModal;
export {
  EditParametersModal_default as default
};
//# sourceMappingURL=EditParametersModal.dev.js.map
