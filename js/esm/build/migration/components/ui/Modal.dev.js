var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A window over the page, in a dialog of the browser: it takes the focus, closes with Escape or with its button and gives the focus
 * back to where the person was.
 *
 * @module     local_filebank/migration/components/ui/Modal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef } from "react";
import Icon from "./Icon";
const Modal = /* @__PURE__ */ __name(({ open, title, onClose, closeLabel, children, footer, size = "medium", busy = false }) => {
  const dialog = useRef(null);
  useEffect(() => {
    const element = dialog.current;
    if (!element) {
      return;
    }
    if (open && !element.open) {
      element.showModal();
    } else if (!open && element.open) {
      element.close();
    }
  }, [open]);
  const close = /* @__PURE__ */ __name(() => {
    if (!busy) {
      onClose();
    }
  }, "close");
  return /* @__PURE__ */ jsxDEV(
    "dialog",
    {
      ref: dialog,
      className: `fbm-dialog fbm-dialog-${size}`,
      onCancel: (event) => {
        event.preventDefault();
        close();
      },
      onClick: (event) => {
        if (event.target === dialog.current) {
          close();
        }
      },
      children: open && /* @__PURE__ */ jsxDEV("div", { className: "fbm-dialog-body", children: [
        /* @__PURE__ */ jsxDEV("header", { className: "fbm-dialog-head", children: [
          /* @__PURE__ */ jsxDEV("h3", { className: "fbm-dialog-title", children: title }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
            lineNumber: 66,
            columnNumber: 17
          }),
          /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-iconbtn", onClick: close, title: closeLabel, disabled: busy, children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "xmark" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
              lineNumber: 68,
              columnNumber: 21
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "fbm-sr", children: closeLabel }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
              lineNumber: 68,
              columnNumber: 42
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
            lineNumber: 67,
            columnNumber: 17
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
          lineNumber: 65,
          columnNumber: 13
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-dialog-scroll", children }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
          lineNumber: 71,
          columnNumber: 13
        }),
        footer && /* @__PURE__ */ jsxDEV("footer", { className: "fbm-dialog-foot", children: footer }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
          lineNumber: 72,
          columnNumber: 24
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
        lineNumber: 64,
        columnNumber: 18
      })
    },
    void 0,
    false,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Modal.tsx",
      lineNumber: 50,
      columnNumber: 12
    }
  );
}, "Modal");
var Modal_default = Modal;
export {
  Modal_default as default
};
//# sourceMappingURL=Modal.dev.js.map
