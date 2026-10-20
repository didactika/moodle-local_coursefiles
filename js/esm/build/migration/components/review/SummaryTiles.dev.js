var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The figures of a migration at the top of its page: what would be copied, overwritten or is already there, what waits for a
 * decision and how many folders it covers. Each one leads to the part of the page that lists what it counts.
 *
 * @module     local_filebank/migration/components/review/SummaryTiles
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import StatTile from "../ui/StatTile";
const SummaryTiles = /* @__PURE__ */ __name(({ summary, text, onFiles, onTab, active }) => {
  const onFilesTab = active.tab === "files";
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-tiles", "data-region": "summary", children: [
    /* @__PURE__ */ jsxDEV(
      StatTile,
      {
        label: text("summary_tocopy"),
        value: summary.tocopy,
        tone: "success",
        icon: "copy",
        hint: text("summary_tocopy_hint"),
        active: onFilesTab && active.bucket === "copy",
        onClick: () => onFiles("copy")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
        lineNumber: 29,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      StatTile,
      {
        label: text("summary_tooverwrite"),
        value: summary.tooverwrite,
        tone: "warning",
        icon: "pen-to-square",
        hint: text("summary_tooverwrite_hint"),
        active: false,
        onClick: () => onFiles("copy")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
        lineNumber: 38,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      StatTile,
      {
        label: text("summary_existing"),
        value: summary.existing,
        tone: "neutral",
        icon: "equals",
        hint: text("summary_existing_hint"),
        active: onFilesTab && active.bucket === "existing",
        onClick: () => onFiles("existing")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
        lineNumber: 47,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      StatTile,
      {
        label: text("summary_left"),
        value: summary.skipped,
        tone: "neutral",
        icon: "hand",
        hint: text("summary_left_hint"),
        active: onFilesTab && active.bucket === "left",
        onClick: () => onFiles("left")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
        lineNumber: 56,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      StatTile,
      {
        label: text("summary_folders"),
        value: summary.folders,
        tone: "primary",
        icon: "folder-tree",
        hint: text("summary_folders_hint", { from: summary.sourcecourses, to: summary.targetcourses }),
        active: active.tab === "folders",
        onClick: () => onTab("folders")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
        lineNumber: 65,
        columnNumber: 9
      }
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/SummaryTiles.tsx",
    lineNumber: 28,
    columnNumber: 12
  });
}, "SummaryTiles");
var SummaryTiles_default = SummaryTiles;
export {
  SummaryTiles_default as default
};
//# sourceMappingURL=SummaryTiles.dev.js.map
