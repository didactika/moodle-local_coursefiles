var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One migration: what it would do, what waits for a decision, its files and folders, and the way to start it. Everything a person
 * does here is saved in this migration; another one is only created from the button that says so.
 *
 * @module     local_filebank/migration/components/review/Review
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useMemo, useState } from "react";
import { useMigration } from "../../hooks/useMigration";
import { describeError } from "../../lib/errors";
import { parametersOf } from "../../lib/parameters";
import { addToast } from "../../lib/toast";
import {
  cancelRun,
  deleteMigrations,
  executeMigration,
  reconfigureMigration,
  resolveDecisions,
  selectFiles,
  selectFolder
} from "../../services/api";
import Alert from "../ui/Alert";
import ConfirmModal from "../ui/ConfirmModal";
import Icon from "../ui/Icon";
import PageHeader from "../ui/PageHeader";
import Skeleton from "../ui/Skeleton";
import StatusBadge from "../ui/StatusBadge";
import ActionPanel from "./ActionPanel";
import DecisionsPanel from "./DecisionsPanel";
import EditParametersModal from "./EditParametersModal";
import FilesPanel from "./FilesPanel";
import FoldersPanel from "./FoldersPanel";
import Notes from "./Notes";
import StartModal from "./StartModal";
import SummaryTiles from "./SummaryTiles";
const Review = /* @__PURE__ */ __name(({ id, text, options, onBack, onNew }) => {
  const state = useMigration(id);
  const { migration, busy } = state;
  const [tab, setTab] = useState("");
  const [bucket, setBucket] = useState("");
  const [starting, setStarting] = useState(false);
  const [stopping, setStopping] = useState(false);
  const [editing, setEditing] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const parameters = useMemo(() => migration ? parametersOf(migration) : null, [migration?.digest]);
  const opened = migration !== null;
  const waiting = migration !== null && migration.natures.length > 0;
  useEffect(() => {
    if (opened && tab === "") {
      setTab(waiting ? "decisions" : "files");
    }
  }, [opened, waiting, tab]);
  if (!migration || !parameters) {
    return /* @__PURE__ */ jsxDEV("div", { className: "fbm-review", children: [
      /* @__PURE__ */ jsxDEV(
        PageHeader,
        {
          title: text("review_title", { n: id }),
          back: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-back", onClick: onBack, children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-left", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 70,
              columnNumber: 83
            }),
            text("back_to_list")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 70,
            columnNumber: 23
          })
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 68,
          columnNumber: 13
        }
      ),
      state.error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, actions: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: () => void state.reload(), children: text("retry") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 72,
        columnNumber: 71
      }), children: state.error }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 72,
        columnNumber: 36
      }),
      state.loading && /* @__PURE__ */ jsxDEV(Skeleton, { rows: 6, label: text("loading") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 75,
        columnNumber: 31
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 67,
      columnNumber: 16
    });
  }
  const decisions = migration.natures.reduce((sum, nature) => sum + nature.count, 0);
  const current = tab === "" ? decisions > 0 ? "decisions" : "files" : tab;
  const resolve = /* @__PURE__ */ __name(async (nature, ids, choice) => {
    const answer = await state.apply(
      () => resolveDecisions(migration.id, migration.digest, nature.kind, ids.length > 0 ? "" : nature.nature, ids, choice),
      (result) => result.migration
    );
    if (answer) {
      void addToast(text("resolved_n", { n: answer.changed }));
    }
    return answer !== null;
  }, "resolve");
  const start = /* @__PURE__ */ __name(async () => {
    const answer = await state.apply(() => executeMigration(migration.id, migration.digest));
    setStarting(false);
    if (answer) {
      void addToast(text(answer.run ? "started" : "nothing_started"), answer.run ? "success" : "info");
    }
  }, "start");
  const stop = /* @__PURE__ */ __name(async () => {
    if (migration.run) {
      await state.apply(() => cancelRun(migration.id, migration.run.id));
    }
    setStopping(false);
  }, "stop");
  const save = /* @__PURE__ */ __name(async (changed) => {
    const answer = await state.apply(() => reconfigureMigration(migration.id, migration.digest, changed));
    if (answer) {
      setEditing(false);
      void addToast(text("edit_saved"));
    }
  }, "save");
  const remove = /* @__PURE__ */ __name(async () => {
    try {
      await deleteMigrations([migration.id]);
      onBack();
    } catch (problem) {
      setDeleting(false);
      void addToast(describeError(problem), "danger");
    }
  }, "remove");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-review", "data-region": "migration-review", "data-migration": migration.id, children: [
    /* @__PURE__ */ jsxDEV(
      PageHeader,
      {
        title: text("review_title", { n: migration.id }),
        badges: /* @__PURE__ */ jsxDEV(StatusBadge, { status: migration.state, label: migration.statelabel }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 129,
          columnNumber: 21
        }),
        back: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-back", onClick: onBack, children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-left", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 130,
            columnNumber: 79
          }),
          text("back_to_list")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 130,
          columnNumber: 19
        }),
        subtitle: /* @__PURE__ */ jsxDEV(Fragment, { children: [
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-route", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: migration.source }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 133,
              columnNumber: 21
            }),
            /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 133,
              columnNumber: 56
            }),
            /* @__PURE__ */ jsxDEV("strong", { children: migration.target }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 133,
              columnNumber: 83
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 132,
            columnNumber: 17
          }),
          " \xB7 ",
          migration.profile,
          " \xB7 ",
          migration.datelabel
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 131,
          columnNumber: 23
        }),
        actions: /* @__PURE__ */ jsxDEV(Fragment, { children: [
          migration.editable && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: () => setEditing(true), disabled: busy, "data-action": "edit-parameters", children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "sliders", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 139,
              columnNumber: 21
            }),
            text("edit_parameters")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 138,
            columnNumber: 40
          }),
          /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: () => onNew(parameters), "data-action": "new-from", children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 142,
              columnNumber: 21
            }),
            text("new_migration")
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 141,
            columnNumber: 17
          }),
          !migration.run?.active && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-iconbtn tone-danger", title: text("delete"), onClick: () => setDeleting(true), children: [
            /* @__PURE__ */ jsxDEV(Icon, { name: "trash" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 145,
              columnNumber: 21
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "fbm-sr", children: text("delete") }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
              lineNumber: 145,
              columnNumber: 42
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 144,
            columnNumber: 44
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 137,
          columnNumber: 22
        })
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 127,
        columnNumber: 9
      }
    ),
    state.stale && /* @__PURE__ */ jsxDEV(Alert, { tone: "warning", live: true, actions: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "fbm-btn", onClick: () => void state.reload(), children: text("reload") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 149,
      columnNumber: 61
    }), children: text("stale") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 149,
      columnNumber: 25
    }),
    state.error !== "" && !state.stale && !editing && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: state.error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 152,
      columnNumber: 60
    }),
    /* @__PURE__ */ jsxDEV(
      SummaryTiles,
      {
        summary: migration.summary,
        text,
        active: { tab: current, bucket },
        onTab: setTab,
        onFiles: (value) => {
          setBucket(value);
          setTab("files");
        }
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 153,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      ActionPanel,
      {
        migration,
        text,
        busy,
        onStart: () => setStarting(true),
        onStop: () => setStopping(true),
        onEdit: () => setEditing(true),
        onRework: () => void state.apply(() => reconfigureMigration(migration.id, migration.digest, parameters))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 163,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(Notes, { notes: migration.summary.notes, text }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 172,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-tabs", role: "tablist", "aria-label": text("review_sections"), children: [
      ["decisions", "hand-pointer", decisions],
      ["files", "file-lines", migration.summary.files],
      ["folders", "folder-tree", migration.summary.folders]
    ].map(([value, icon, count]) => /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        role: "tab",
        "aria-selected": current === value,
        className: `fbm-tab${current === value ? " is-on" : ""}${value === "decisions" && decisions > 0 ? " needs-attention" : ""}`,
        onClick: () => setTab(value),
        "data-tab": value,
        children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: icon, gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 187,
            columnNumber: 17
          }),
          text("tab_" + value),
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-count", children: count }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 187,
            columnNumber: 63
          })
        ]
      },
      value,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 178,
        columnNumber: 78
      }
    )) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 173,
      columnNumber: 9
    }),
    current === "decisions" && /* @__PURE__ */ jsxDEV(DecisionsPanel, { migration, text, busy, onResolve: resolve }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 190,
      columnNumber: 37
    }),
    current === "files" && /* @__PURE__ */ jsxDEV(
      FilesPanel,
      {
        migration,
        text,
        bucket,
        onBucket: setBucket,
        busy,
        onSelect: (action, visible, included) => void state.apply(() => selectFiles(migration.id, migration.digest, action, visible, included))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 191,
        columnNumber: 33
      }
    ),
    current === "folders" && /* @__PURE__ */ jsxDEV(
      FoldersPanel,
      {
        migration,
        text,
        busy,
        onSelect: (folder, included) => void state.apply(() => selectFolder(migration.id, migration.digest, folder, included))
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 199,
        columnNumber: 35
      }
    ),
    /* @__PURE__ */ jsxDEV(StartModal, { open: starting, migration, text, busy, onConfirm: () => void start(), onCancel: () => setStarting(false) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
      lineNumber: 205,
      columnNumber: 9
    }),
    /* @__PURE__ */ jsxDEV(
      ConfirmModal,
      {
        open: stopping,
        tone: "danger",
        icon: "stop",
        busy,
        title: text("stop_title"),
        confirmLabel: text("run_stop"),
        cancelLabel: text("cancel"),
        onConfirm: () => void stop(),
        onCancel: () => setStopping(false),
        children: /* @__PURE__ */ jsxDEV("p", { children: text("stop_text") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
          lineNumber: 217,
          columnNumber: 13
        })
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 206,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      ConfirmModal,
      {
        open: deleting,
        tone: "danger",
        icon: "triangle-exclamation",
        title: text("delete_title"),
        confirmLabel: text("delete"),
        cancelLabel: text("cancel"),
        onConfirm: () => void remove(),
        onCancel: () => setDeleting(false),
        children: [
          /* @__PURE__ */ jsxDEV("p", { children: text("delete_text", { n: 1 }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 229,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("p", { className: "fbm-muted", children: text("delete_hint") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
            lineNumber: 230,
            columnNumber: 13
          })
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 219,
        columnNumber: 9
      }
    ),
    /* @__PURE__ */ jsxDEV(
      EditParametersModal,
      {
        open: editing,
        options,
        initial: parameters,
        text,
        busy,
        error: state.error,
        onSave: (changed) => void save(changed),
        onClose: () => {
          setEditing(false);
          state.clearError();
        }
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
        lineNumber: 232,
        columnNumber: 9
      }
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/Review.tsx",
    lineNumber: 126,
    columnNumber: 12
  });
}, "Review");
var Review_default = Review;
export {
  Review_default as default
};
//# sourceMappingURL=Review.dev.js.map
