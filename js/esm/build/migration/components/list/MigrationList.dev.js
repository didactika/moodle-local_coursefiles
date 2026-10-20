var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of the site: filtered, searched and paged by the server, with their state and what is left to decide in each one.
 *
 * @module     local_filebank/migration/components/list/MigrationList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useCallback, useEffect, useState } from "react";
import { useDebounced } from "../../hooks/useDebounced";
import { useKeptHeight } from "../../hooks/useKeptHeight";
import { PAGE_SIZES } from "../../lib/paging";
import { describeError } from "../../lib/errors";
import { deleteMigrations, listMigrations } from "../../services/api";
import Alert from "../ui/Alert";
import Check from "../ui/Check";
import FilterMenu from "../ui/FilterMenu";
import IconButton from "../ui/IconButton";
import ConfirmModal from "../ui/ConfirmModal";
import EmptyState from "../ui/EmptyState";
import FloatingBar from "../ui/FloatingBar";
import Icon from "../ui/Icon";
import PageHeader from "../ui/PageHeader";
import Pagination from "../ui/Pagination";
import Progress from "../ui/Progress";
import SearchBox from "../ui/SearchBox";
import Skeleton from "../ui/Skeleton";
import StatusBadge from "../ui/StatusBadge";
const STATES = ["", "reviewable", "queued", "running", "partial", "completed", "cancelled"];
const MigrationList = /* @__PURE__ */ __name(({ text, onOpen, onNew }) => {
  const [search, setSearch] = useState("");
  const [state, setState] = useState("");
  const [page, setPage] = useState(0);
  const [perpage, setPerpage] = useState(PAGE_SIZES[0]);
  const kept = useKeptHeight();
  const [data, setData] = useState(null);
  const [error, setError] = useState("");
  const [selected, setSelected] = useState([]);
  const [asking, setAsking] = useState(null);
  const [deleting, setDeleting] = useState(false);
  const query = useDebounced(search, 250);
  const load = useCallback(async () => {
    try {
      setData(await listMigrations(query, state, page, perpage));
      setError("");
    } catch (problem) {
      setError(describeError(problem));
    }
  }, [query, state, page, perpage]);
  useEffect(() => {
    void load();
  }, [load]);
  useEffect(() => {
    setPage(0);
    kept.release();
  }, [query, state, perpage]);
  const active = data?.migrations.some((migration) => migration.state === "queued" || migration.state === "running") ?? false;
  useEffect(() => {
    if (!active) {
      return void 0;
    }
    const timer = window.setInterval(() => void load(), 4e3);
    return () => window.clearInterval(timer);
  }, [active, load]);
  const countOf = /* @__PURE__ */ __name((value) => data?.counts.find((item) => item.state === (value === "" ? "all" : value))?.count ?? 0, "countOf");
  const rows = data?.migrations ?? [];
  const hasAny = data !== null && (countOf("") > 0 || search !== "" || state !== "");
  const chosen = selected.filter((id) => rows.some((row) => row.id === id));
  const deletable = rows.filter((row) => row.candelete);
  const remove = /* @__PURE__ */ __name(async () => {
    if (!asking) {
      return;
    }
    setDeleting(true);
    try {
      await deleteMigrations(asking);
      setSelected([]);
      setAsking(null);
      await load();
    } catch (problem) {
      setError(describeError(problem));
      setAsking(null);
    } finally {
      setDeleting(false);
    }
  }, "remove");
  return /* @__PURE__ */ jsxDEV("div", { className: "fbm-list", "data-region": "migration-list", children: [
    /* @__PURE__ */ jsxDEV(
      PageHeader,
      {
        title: text("list_title"),
        badges: countOf("") > 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge rounded-pill fbm-count", children: countOf("") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 110,
          columnNumber: 40
        }),
        subtitle: text("list_subtitle")
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 108,
        columnNumber: 9
      }
    ),
    error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
      lineNumber: 113,
      columnNumber: 26
    }),
    data === null && error === "" && /* @__PURE__ */ jsxDEV(Skeleton, { rows: 5, label: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
      lineNumber: 114,
      columnNumber: 43
    }),
    data !== null && data.counts.find((item) => item.state === "all")?.count === 0 && search === "" && state === "" && /* @__PURE__ */ jsxDEV(
      EmptyState,
      {
        icon: "right-left",
        title: text("list_empty_title"),
        text: text("list_empty_text"),
        actions: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", onClick: onNew, "data-action": "new-migration", children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 120,
            columnNumber: 17
          }),
          text("new_migration")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 119,
          columnNumber: 22
        })
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 115,
        columnNumber: 125
      }
    ),
    data !== null && (countOf("") > 0 || search !== "" || state !== "") && /* @__PURE__ */ jsxDEV(Fragment, { children: [
      /* @__PURE__ */ jsxDEV("div", { className: "fbm-toolbar", children: [
        /* @__PURE__ */ jsxDEV(SearchBox, { id: "fbm-list-search", value: search, onChange: setSearch, placeholder: text("list_search"), clearLabel: text("clear") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 125,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV(
          FilterMenu,
          {
            label: text("filters"),
            resetLabel: text("filters_reset"),
            groups: [{
              id: "state",
              label: text("list_states"),
              value: state,
              onChange: setState,
              options: STATES.map((value) => ({
                value,
                label: value === "" ? text("all") : text("state_" + value),
                count: countOf(value)
              }))
            }]
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 126,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-toolbar-actions", children: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", onClick: onNew, "data-action": "new-migration", children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "plus", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 143,
            columnNumber: 25
          }),
          text("new_migration")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 142,
          columnNumber: 21
        }) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 141,
          columnNumber: 17
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 124,
        columnNumber: 13
      }),
      rows.length === 0 ? /* @__PURE__ */ jsxDEV(EmptyState, { icon: "magnifying-glass", title: text("list_nomatch_title"), text: text("list_nomatch_text") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 148,
        columnNumber: 19
      }) : /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", ref: kept.ref, style: kept.style, children: /* @__PURE__ */ jsxDEV("table", { className: "table table-hover", children: [
        /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
          /* @__PURE__ */ jsxDEV("th", { className: "fbm-col-check", children: /* @__PURE__ */ jsxDEV(
            Check,
            {
              label: text("select_all_page"),
              checked: deletable.length > 0 && deletable.every((row) => chosen.includes(row.id)),
              partial: chosen.length > 0,
              disabled: deletable.length === 0,
              onChange: (checked) => setSelected(checked ? deletable.map((row) => row.id) : [])
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 154,
              columnNumber: 37
            }
          ) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 153,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: text("col_migration") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 162,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: text("col_route") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 163,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: text("col_files") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 164,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: text("col_state") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 165,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: text("col_created") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 166,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("th", { children: /* @__PURE__ */ jsxDEV("span", { className: "filebank-visually-hidden", children: text("col_actions") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 167,
            columnNumber: 37
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 167,
            columnNumber: 33
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 152,
          columnNumber: 29
        }) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 151,
          columnNumber: 25
        }),
        /* @__PURE__ */ jsxDEV("tbody", { children: rows.map((row) => /* @__PURE__ */ jsxDEV("tr", { "data-migration": row.id, children: [
          /* @__PURE__ */ jsxDEV("td", { className: "fbm-col-check", children: /* @__PURE__ */ jsxDEV(
            Check,
            {
              label: text("select_row", { n: row.id }),
              checked: chosen.includes(row.id),
              disabled: !row.candelete,
              onChange: (checked) => setSelected(checked ? [...chosen, row.id] : chosen.filter((id) => id !== row.id))
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 173,
              columnNumber: 37
            }
          ) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 172,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { children: [
            /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-link p-0 fbm-link fbm-row-title", onClick: () => onOpen(row.id), children: text("review_title", { n: row.id }) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 181,
              columnNumber: 37
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "fbm-table-meta", children: row.profile }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 184,
              columnNumber: 37
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 180,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: "fbm-route", children: [
            /* @__PURE__ */ jsxDEV("span", { children: row.source }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 188,
              columnNumber: 41
            }),
            /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 188,
              columnNumber: 66
            }),
            /* @__PURE__ */ jsxDEV("span", { children: row.target }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 188,
              columnNumber: 93
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 187,
            columnNumber: 37
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 186,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: "fbm-files-cell", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: row.towrite }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 193,
              columnNumber: 41
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: text("list_of_files", { n: row.files }) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 194,
              columnNumber: 41
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 192,
            columnNumber: 37
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 191,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { children: [
            /* @__PURE__ */ jsxDEV(StatusBadge, { status: row.state, label: row.statelabel }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 198,
              columnNumber: 37
            }),
            (row.state === "running" || row.state === "queued") && /* @__PURE__ */ jsxDEV(
              Progress,
              {
                value: row.done,
                max: Math.max(1, row.towrite),
                label: text("progress")
              },
              void 0,
              false,
              {
                fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
                lineNumber: 199,
                columnNumber: 93
              }
            )
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 197,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { className: "text-muted", children: row.datelabel }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 205,
            columnNumber: 33
          }),
          /* @__PURE__ */ jsxDEV("td", { className: "fbm-col-actions", children: /* @__PURE__ */ jsxDEV("div", { className: "fbm-actions", children: [
            /* @__PURE__ */ jsxDEV(IconButton, { icon: "eye", label: text("open"), tone: "primary", onClick: () => onOpen(row.id) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 208,
              columnNumber: 41
            }),
            row.candelete && /* @__PURE__ */ jsxDEV(IconButton, { icon: "trash", label: text("delete"), tone: "danger", onClick: () => setAsking([row.id]) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
              lineNumber: 209,
              columnNumber: 59
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 207,
            columnNumber: 37
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 206,
            columnNumber: 33
          })
        ] }, row.id, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 171,
          columnNumber: 48
        })) }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 170,
          columnNumber: 25
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 150,
        columnNumber: 21
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 149,
        columnNumber: 19
      }),
      /* @__PURE__ */ jsxDEV(
        Pagination,
        {
          page,
          perpage,
          total: data.total,
          onPage: (next) => {
            kept.keep();
            setPage(next);
          },
          text,
          onPerPage: setPerpage
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 216,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
      lineNumber: 123,
      columnNumber: 81
    }),
    chosen.length > 0 && /* @__PURE__ */ jsxDEV(
      FloatingBar,
      {
        summary: text("selected_n", { n: chosen.length }),
        clearLabel: text("clear_selection"),
        onClear: () => setSelected([]),
        children: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-sm btn-danger", onClick: () => setAsking(chosen), children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "trash", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 234,
            columnNumber: 17
          }),
          text("delete_selected")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
          lineNumber: 233,
          columnNumber: 13
        })
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 228,
        columnNumber: 31
      }
    ),
    /* @__PURE__ */ jsxDEV(
      ConfirmModal,
      {
        open: asking !== null,
        tone: "danger",
        icon: "triangle-exclamation",
        busy: deleting,
        title: text("delete_title"),
        confirmLabel: text("delete"),
        cancelLabel: text("cancel"),
        onConfirm: () => void remove(),
        onCancel: () => setAsking(null),
        children: [
          /* @__PURE__ */ jsxDEV("p", { children: text("delete_text", { n: asking?.length ?? 0 }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 248,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV("p", { className: "text-muted", children: text("delete_hint") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
            lineNumber: 249,
            columnNumber: 13
          })
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
        lineNumber: 237,
        columnNumber: 9
      }
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/list/MigrationList.tsx",
    lineNumber: 107,
    columnNumber: 12
  });
}, "MigrationList");
var MigrationList_default = MigrationList;
export {
  MigrationList_default as default
};
//# sourceMappingURL=MigrationList.dev.js.map
