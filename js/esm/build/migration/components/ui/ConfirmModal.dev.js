var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A question that must be answered before something that is hard to undo, in a dialog that takes the focus and closes with Escape.
 *
 * @module     local_filebank/migration/components/ui/ConfirmModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Icon from "./Icon";
import Modal from "./Modal";
const ConfirmModal = /* @__PURE__ */ __name(({
  open,
  title,
  children,
  confirmLabel,
  cancelLabel,
  onConfirm,
  onCancel,
  tone = "primary",
  icon = "circle-question",
  busy = false
}) => /* @__PURE__ */ jsxDEV(
  Modal,
  {
    open,
    size: "small",
    busy,
    title: /* @__PURE__ */ jsxDEV(Fragment, { children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: icon, gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
        lineNumber: 37,
        columnNumber: 14
      }),
      title
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
      lineNumber: 37,
      columnNumber: 12
    }),
    onClose: onCancel,
    closeLabel: cancelLabel,
    footer: /* @__PURE__ */ jsxDEV(Fragment, { children: [
      /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: onCancel, disabled: busy, children: cancelLabel }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
        lineNumber: 41,
        columnNumber: 9
      }),
      /* @__PURE__ */ jsxDEV("button", { type: "button", className: `fbm-btn fbm-btn-${tone}`, onClick: onConfirm, disabled: busy, children: [
        busy && /* @__PURE__ */ jsxDEV(Icon, { name: "spinner", gap: true, spin: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
          lineNumber: 43,
          columnNumber: 22
        }),
        confirmLabel
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
        lineNumber: 42,
        columnNumber: 9
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
      lineNumber: 40,
      columnNumber: 13
    }),
    children: /* @__PURE__ */ jsxDEV("div", { className: "fbm-confirm-body", children }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
      lineNumber: 47,
      columnNumber: 5
    })
  },
  void 0,
  false,
  {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/ConfirmModal.tsx",
    lineNumber: 33,
    columnNumber: 26
  }
), "ConfirmModal");
var ConfirmModal_default = ConfirmModal;
export {
  ConfirmModal_default as default
};
//# sourceMappingURL=ConfirmModal.dev.js.map
