var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What the migration left out because it did not match: a course without a folder in the source, a destination that has no course
 * for the filters, a hidden folder. None of it is an error, so it is only said, and a person can open it or leave it closed.
 *
 * @module     local_filebank/migration/components/review/Notes
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState } from "react";
import Icon from "../ui/Icon";
const Notes = /* @__PURE__ */ __name(({ notes, text }) => {
  const [open, setOpen] = useState(false);
  if (notes.length === 0) {
    return null;
  }
  const total = notes.reduce((sum, note) => sum + note.count, 0);
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-notes-box", "data-region": "notes", children: [
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-notes-toggle", "aria-expanded": open, onClick: () => setOpen(!open), children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "circle-info", gap: true }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
        lineNumber: 30,
        columnNumber: 13
      }),
      text("notes_title", { n: total }),
      /* @__PURE__ */ jsxDEV(Icon, { name: open ? "chevron-up" : "chevron-down" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
        lineNumber: 32,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
      lineNumber: 29,
      columnNumber: 9
    }),
    open && /* @__PURE__ */ jsxDEV("ul", { className: "fbm-notes", children: [
      notes.map((note) => /* @__PURE__ */ jsxDEV("li", { children: note.label }, note.kind, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
        lineNumber: 35,
        columnNumber: 34
      })),
      /* @__PURE__ */ jsxDEV("li", { className: "fbm-muted", children: text("notes_footer") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
        lineNumber: 36,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
      lineNumber: 34,
      columnNumber: 18
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/Notes.tsx",
    lineNumber: 28,
    columnNumber: 12
  });
}, "Notes");
var Notes_default = Notes;
export {
  Notes_default as default
};
//# sourceMappingURL=Notes.dev.js.map
