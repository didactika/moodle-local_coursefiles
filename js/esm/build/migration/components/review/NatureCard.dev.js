var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One kind of thing that waits for a decision, with what can be done to all of it at once. Two hundred files that are in conflict
 * in the same way are settled with one click and one question, and can still be looked at one by one.
 *
 * @module     local_filebank/migration/components/review/NatureCard
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState } from "react";
import ConfirmModal from "../ui/ConfirmModal";
import Icon from "../ui/Icon";
import DecisionList from "./DecisionList";
const NatureCard = /* @__PURE__ */ __name(({ migrationId, digest, nature, text, busy, onResolve }) => {
  const [open, setOpen] = useState(false);
  const [asking, setAsking] = useState("");
  const label = nature.choices.find((choice) => choice.value === asking)?.label ?? "";
  const confirm = /* @__PURE__ */ __name(async () => {
    const done = await onResolve(nature, [], asking);
    if (done) {
      setAsking("");
    }
  }, "confirm");
  return /* @__PURE__ */ jsxDEV("section", { className: "fbm-nature", "data-nature": nature.nature, children: [
    /* @__PURE__ */ jsxDEV("header", { className: "fbm-nature-head", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-nature-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: nature.kind === "file" ? "file-circle-exclamation" : "folder-open" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 43,
        columnNumber: 47
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 43,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-nature-title", children: [
        /* @__PURE__ */ jsxDEV("h4", { className: "fbm-h4", children: [
          nature.label,
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-count", children: nature.count }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
            lineNumber: 45,
            columnNumber: 54
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
          lineNumber: 45,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted fbm-nature-help", children: text("nature_help_" + nature.kind) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
          lineNumber: 46,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 44,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
      lineNumber: 42,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-nature-actions", children: [
      nature.choices.map((choice) => /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: `fbm-btn ${choice.value === "overwrite" ? "fbm-btn-warning" : "fbm-btn-primary"}`,
          disabled: busy,
          onClick: () => setAsking(choice.value),
          "data-choice": choice.value,
          children: [
            text("apply_all", { n: nature.count }),
            ": ",
            choice.label
          ]
        },
        choice.value,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
          lineNumber: 50,
          columnNumber: 45
        }
      )),
      /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", "aria-expanded": open, onClick: () => setOpen(!open), children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: open ? "chevron-up" : "list", gap: true }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
          lineNumber: 61,
          columnNumber: 17
        }),
        open ? text("hide_list") : text("review_one_by_one")
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 60,
        columnNumber: 13
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
      lineNumber: 49,
      columnNumber: 9
    }),
    open && /* @__PURE__ */ jsxDEV(
      DecisionList,
      {
        migrationId,
        digest,
        nature,
        text,
        busy,
        onApply: (ids, choice) => void onResolve(nature, ids, choice)
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 64,
        columnNumber: 18
      }
    ),
    /* @__PURE__ */ jsxDEV(
      ConfirmModal,
      {
        open: asking !== "",
        tone: asking === "overwrite" ? "warning" : "primary",
        icon: asking === "overwrite" ? "triangle-exclamation" : "circle-question",
        busy,
        title: text("apply_all_title", { n: nature.count }),
        confirmLabel: text("apply"),
        cancelLabel: text("cancel"),
        onConfirm: () => void confirm(),
        onCancel: () => setAsking(""),
        children: [
          /* @__PURE__ */ jsxDEV("p", { children: /* @__PURE__ */ jsxDEV("strong", { children: nature.label }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
            lineNumber: 83,
            columnNumber: 16
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
            lineNumber: 83,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("p", { children: label }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
            lineNumber: 84,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted", children: text("apply_all_text", { n: nature.count }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
            lineNumber: 85,
            columnNumber: 13
          })
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
        lineNumber: 72,
        columnNumber: 9
      }
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/NatureCard.tsx",
    lineNumber: 41,
    columnNumber: 12
  });
}, "NatureCard");
var NatureCard_default = NatureCard;
export {
  NatureCard_default as default
};
//# sourceMappingURL=NatureCard.dev.js.map
