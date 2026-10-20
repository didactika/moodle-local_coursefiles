var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The last question before a migration writes anything: what it will do, with the files that overwrite another one said apart,
 * and what it will leave as it is.
 *
 * @module     local_filebank/migration/components/review/StartModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Alert from "../ui/Alert";
import ConfirmModal from "../ui/ConfirmModal";
const StartModal = /* @__PURE__ */ __name(({ open, migration, text, busy, onConfirm, onCancel }) => {
  const { summary } = migration;
  return /* @__PURE__ */ jsxDEV(
    ConfirmModal,
    {
      open,
      busy,
      title: text("start_title", { n: migration.id }),
      confirmLabel: text("start"),
      cancelLabel: text("cancel"),
      onConfirm,
      onCancel,
      children: [
        /* @__PURE__ */ jsxDEV("ul", { className: "fbm-confirm-list", children: [
          /* @__PURE__ */ jsxDEV("li", { children: [
            /* @__PURE__ */ jsxDEV("strong", { children: summary.tocopy }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
              lineNumber: 38,
              columnNumber: 17
            }),
            " ",
            text("start_copy")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
            lineNumber: 38,
            columnNumber: 13
          }),
          summary.tooverwrite > 0 && /* @__PURE__ */ jsxDEV("li", { className: "is-warning", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: summary.tooverwrite }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
              lineNumber: 39,
              columnNumber: 68
            }),
            " ",
            text("start_overwrite")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
            lineNumber: 39,
            columnNumber: 41
          }),
          /* @__PURE__ */ jsxDEV("li", { className: "text-muted", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: summary.existing }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
              lineNumber: 40,
              columnNumber: 40
            }),
            " ",
            text("start_existing")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
            lineNumber: 40,
            columnNumber: 13
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
          lineNumber: 37,
          columnNumber: 9
        }),
        summary.tooverwrite > 0 && /* @__PURE__ */ jsxDEV(Alert, { tone: "warning", children: text("start_overwrite_warning") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
          lineNumber: 42,
          columnNumber: 37
        }),
        /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: text("start_background") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
          lineNumber: 43,
          columnNumber: 9
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/StartModal.tsx",
      lineNumber: 28,
      columnNumber: 12
    }
  );
}, "StartModal");
var StartModal_default = StartModal;
export {
  StartModal_default as default
};
//# sourceMappingURL=StartModal.dev.js.map
