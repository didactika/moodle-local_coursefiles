var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The badge of a state, with its icon and its text, in the colour of what it says.
 *
 * @module     local_filebank/migration/components/ui/StatusBadge
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { badgeClasses } from "../../lib/tones";
import Icon from "./Icon";
const LOOKS = {
  reviewable: { tone: "info", icon: "clipboard-check" },
  queued: { tone: "warning", icon: "clock" },
  running: { tone: "primary", icon: "spinner" },
  partial: { tone: "warning", icon: "triangle-exclamation" },
  completed: { tone: "success", icon: "circle-check" },
  cancelled: { tone: "neutral", icon: "ban" },
  blocked: { tone: "danger", icon: "circle-xmark" },
  copy: { tone: "success", icon: "copy" },
  overwrite: { tone: "warning", icon: "pen-to-square" },
  skip_identical: { tone: "neutral", icon: "equals" },
  skip_content: { tone: "neutral", icon: "equals" },
  skip_conflict: { tone: "neutral", icon: "hand" },
  pending: { tone: "neutral", icon: "hourglass-half" },
  copied: { tone: "success", icon: "circle-check" },
  done: { tone: "success", icon: "circle-check" }
};
const lookOf = /* @__PURE__ */ __name((status) => {
  if (status.startsWith("blocked")) {
    return { tone: "danger", icon: "circle-exclamation" };
  }
  return LOOKS[status] ?? { tone: "neutral", icon: "circle" };
}, "lookOf");
const StatusBadge = /* @__PURE__ */ __name(({ status, label }) => {
  const look = lookOf(status);
  return /* @__PURE__ */ jsxDEV("span", { className: badgeClasses(look.tone), children: [
    /* @__PURE__ */ jsxDEV(Icon, { name: look.icon, gap: true, spin: status === "running" }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatusBadge.tsx",
      lineNumber: 60,
      columnNumber: 9
    }),
    label
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/ui/StatusBadge.tsx",
    lineNumber: 59,
    columnNumber: 12
  });
}, "StatusBadge");
var StatusBadge_default = StatusBadge;
export {
  StatusBadge_default as default
};
//# sourceMappingURL=StatusBadge.dev.js.map
