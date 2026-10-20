var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Everything that waits for a decision in a migration, grouped by its nature. None of it stops the migration: what is not decided
 * stays out of it, and what is decided is saved in the same migration.
 *
 * @module     local_filebank/migration/components/review/DecisionsPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import EmptyState from "../ui/EmptyState";
import Panel from "../ui/Panel";
import NatureCard from "./NatureCard";
const DecisionsPanel = /* @__PURE__ */ __name(({ migration, text, busy, onResolve }) => {
  const settled = migration.summary.skipped > 0;
  return /* @__PURE__ */ jsxDEV(Panel, { title: text("tab_decisions"), icon: "hand-pointer", count: migration.natures.reduce((sum, item) => sum + item.count, 0), region: "decisions", children: migration.natures.length === 0 ? /* @__PURE__ */ jsxDEV(EmptyState, { plain: true, icon: "circle-check", title: text("decisions_none_title"), text: text(settled ? "decisions_none_settled" : "decisions_none_text") }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
    lineNumber: 29,
    columnNumber: 15
  }) : /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-panel-intro", children: text("decisions_intro") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
      lineNumber: 31,
      columnNumber: 17
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-natures", children: migration.natures.map((nature) => /* @__PURE__ */ jsxDEV(
      NatureCard,
      {
        migrationId: migration.id,
        digest: migration.digest,
        nature,
        text,
        busy: busy || !migration.editable,
        onResolve
      },
      nature.kind + nature.nature,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
        lineNumber: 33,
        columnNumber: 56
      }
    )) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
      lineNumber: 32,
      columnNumber: 17
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
    lineNumber: 30,
    columnNumber: 15
  }) }, void 0, false, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/DecisionsPanel.tsx",
    lineNumber: 27,
    columnNumber: 12
  });
}, "DecisionsPanel");
var DecisionsPanel_default = DecisionsPanel;
export {
  DecisionsPanel_default as default
};
//# sourceMappingURL=DecisionsPanel.dev.js.map
