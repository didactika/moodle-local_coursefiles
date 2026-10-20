var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question before something that matters, in the save and cancel modal of Moodle: its header, its buttons and its look are the
 * ones of the theme, and only its body is drawn here. Cancel, its close button and Escape answer no.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { requireAsync } from "@moodle/lms/core/amd";
const unwrap = /* @__PURE__ */ __name((module) => module.default ?? module, "unwrap");
const ConfirmModal = /* @__PURE__ */ __name(({
  open,
  title,
  children,
  confirmLabel,
  cancelLabel,
  onConfirm,
  onCancel,
  tone = "primary",
  busy = false
}) => {
  const [body, setBody] = useState(null);
  const current = useRef(null);
  const answers = useRef({ onConfirm, onCancel });
  answers.current = { onConfirm, onCancel };
  useEffect(() => {
    if (!open) {
      return void 0;
    }
    let alive = true;
    let made = null;
    void (async () => {
      const [factory, events] = await Promise.all([
        requireAsync("core/modal_save_cancel").then(unwrap),
        requireAsync("core/modal_events").then(unwrap)
      ]);
      const modal = await factory.create({ title, body: "", removeOnClose: true });
      if (!alive) {
        modal.destroy();
        return;
      }
      made = modal;
      current.current = modal;
      modal.setSaveButtonText(confirmLabel);
      const root = modal.getRoot();
      const save = root[0].querySelector('[data-action="save"]');
      if (save && tone !== "primary") {
        save.classList.replace("btn-primary", `btn-${tone}`);
      }
      const cancel = root[0].querySelector('[data-action="cancel"]');
      if (cancel) {
        cancel.textContent = cancelLabel;
      }
      root.on(events.save, (event) => {
        event.preventDefault();
        answers.current.onConfirm();
      });
      root.on(events.hidden, () => {
        if (current.current === modal) {
          current.current = null;
          answers.current.onCancel();
        }
      });
      modal.show();
      setBody(modal.getBody()[0]);
    })();
    return () => {
      alive = false;
      setBody(null);
      if (made && current.current === made) {
        current.current = null;
        made.hide();
      }
    };
  }, [open]);
  useEffect(() => {
    body?.closest(".modal-content")?.querySelectorAll(".modal-footer button, .modal-header button").forEach((button) => {
      button.disabled = busy;
    });
  }, [busy, body]);
  return body ? createPortal(/* @__PURE__ */ jsxDEV("div", { className: "filebank-ui fbm-confirm-body", children }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
    lineNumber: 128,
    columnNumber: 32
  }), body) : null;
}, "ConfirmModal");
var ConfirmModal_default = ConfirmModal;
export {
  ConfirmModal_default as default
};
//# sourceMappingURL=ConfirmModal.dev.js.map
