var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The files of a migration: filtered by what would happen to them, searched and paged by the server. A file is put in or out of
 * the migration with its check box, and every file of the migration with one button.
 *
 * @module     local_filebank/migration/components/review/FilesPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { useDebounced } from "../../hooks/useDebounced";
import { PAGE_SIZES } from "../../lib/paging";
import { describeError } from "../../lib/errors";
import { getFiles } from "../../services/api";
import Alert from "../ui/Alert";
import Check from "../ui/Check";
import FilterMenu from "../ui/FilterMenu";
import EmptyState from "../ui/EmptyState";
import Icon from "../ui/Icon";
import Panel from "../ui/Panel";
import Pagination from "../ui/Pagination";
import SearchBox from "../ui/SearchBox";
import Skeleton from "../ui/Skeleton";
import StatusBadge from "../ui/StatusBadge";
const BUCKETS = ["", "copy", "existing", "left"];
const FilesPanel = /* @__PURE__ */ __name(({ migration, text, bucket, onBucket, busy, onSelect }) => {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(0);
  const [perpage, setPerpage] = useState(PAGE_SIZES[0]);
  const [data, setData] = useState(null);
  const [error, setError] = useState("");
  const query = useDebounced(search, 250);
  useEffect(() => {
    setPage(0);
  }, [bucket, query, perpage]);
  useEffect(() => {
    let current = true;
    void getFiles(migration.id, bucket, query, page, perpage).then((read) => {
      if (current) {
        setData(read);
        setError("");
      }
    }).catch((problem) => {
      if (current) {
        setError(describeError(problem));
      }
    });
    return () => {
      current = false;
    };
  }, [migration.id, migration.digest, bucket, query, page, perpage]);
  const countOf = /* @__PURE__ */ __name((value) => data?.counts.find((item) => item.bucket === (value === "" ? "all" : value))?.count ?? 0, "countOf");
  const files = data?.files ?? [];
  const editable = migration.editable && !busy;
  const settable = files.filter((file) => file.settable);
  const included = files.filter((file) => file.included);
  const showsRun = migration.run !== void 0;
  const toggle = /* @__PURE__ */ __name((id, checked) => onSelect(
    "page",
    files.map((file) => file.id),
    files.filter((file) => file.id === id ? checked : file.included).map((file) => file.id)
  ), "toggle");
  const togglePage = /* @__PURE__ */ __name((checked) => onSelect(
    "page",
    files.map((file) => file.id),
    files.filter((file) => file.settable ? checked : file.included).map((file) => file.id)
  ), "togglePage");
  return /* @__PURE__ */ jsxDEV(
    Panel,
    {
      title: text("tab_files"),
      icon: "file-lines",
      count: countOf(""),
      region: "files",
      actions: migration.editable && /* @__PURE__ */ jsxDEV(Fragment, { children: [
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary btn-sm", disabled: busy, onClick: () => onSelect("all"), children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "square-check", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
            lineNumber: 99,
            columnNumber: 17
          }),
          text("include_all")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 98,
          columnNumber: 13
        }),
        /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary btn-sm", disabled: busy, onClick: () => onSelect("none"), children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "square", gap: true }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
            lineNumber: 102,
            columnNumber: 17
          }),
          text("exclude_all")
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 101,
          columnNumber: 13
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
        lineNumber: 97,
        columnNumber: 40
      }),
      children: [
        error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 106,
          columnNumber: 26
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-toolbar", children: [
          /* @__PURE__ */ jsxDEV(SearchBox, { id: "fbm-files-search", value: search, onChange: setSearch, placeholder: text("files_search"), clearLabel: text("clear") }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
            lineNumber: 108,
            columnNumber: 13
          }),
          /* @__PURE__ */ jsxDEV(
            FilterMenu,
            {
              label: text("filters"),
              resetLabel: text("filters_reset"),
              groups: [{
                id: "bucket",
                label: text("files_groups"),
                value: bucket,
                onChange: /* @__PURE__ */ __name((value) => onBucket(value), "onChange"),
                options: BUCKETS.map((value) => ({
                  value,
                  label: text(value === "" ? "all" : "bucket_" + value),
                  count: countOf(value)
                }))
              }]
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 109,
              columnNumber: 13
            }
          )
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 107,
          columnNumber: 9
        }),
        data === null && error === "" && /* @__PURE__ */ jsxDEV(Skeleton, { rows: 6, label: text("loading") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 125,
          columnNumber: 43
        }),
        data !== null && files.length === 0 && /* @__PURE__ */ jsxDEV(EmptyState, { plain: true, icon: "file-circle-question", title: text("files_empty_title"), text: text("files_empty_text") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 126,
          columnNumber: 49
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-list-region", children: [
          files.length > 0 && /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", children: /* @__PURE__ */ jsxDEV("table", { className: "table table-hover fbm-files", children: [
            /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV("th", { className: "fbm-col-check", children: /* @__PURE__ */ jsxDEV(
                Check,
                {
                  label: text("select_all_page"),
                  checked: settable.length > 0 && settable.every((file) => file.included),
                  partial: included.length > 0,
                  disabled: !editable || settable.length === 0,
                  onChange: togglePage
                },
                void 0,
                false,
                {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 133,
                  columnNumber: 29
                }
              ) }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 132,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("th", { children: text("col_file") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 141,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("th", { children: text("col_from") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 142,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("th", { children: text("col_to") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 143,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("th", { children: text("col_decision") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 144,
                columnNumber: 25
              }),
              showsRun && /* @__PURE__ */ jsxDEV("th", { children: text("col_result") }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 145,
                columnNumber: 38
              })
            ] }, void 0, true, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 131,
              columnNumber: 21
            }) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 130,
              columnNumber: 17
            }),
            /* @__PURE__ */ jsxDEV("tbody", { children: files.map((file) => /* @__PURE__ */ jsxDEV("tr", { className: file.included ? "" : "is-out", "data-file": file.id, children: [
              /* @__PURE__ */ jsxDEV("td", { className: "fbm-col-check", children: /* @__PURE__ */ jsxDEV(
                Check,
                {
                  label: file.filename,
                  checked: file.included,
                  disabled: !editable || !file.settable,
                  onChange: (checked) => toggle(file.id, checked)
                },
                void 0,
                false,
                {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 151,
                  columnNumber: 29
                }
              ) }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 150,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("td", { children: [
                /* @__PURE__ */ jsxDEV("strong", { className: "fbm-file-name", children: file.filename }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 159,
                  columnNumber: 29
                }),
                file.filepath !== "/" && /* @__PURE__ */ jsxDEV("span", { className: "text-muted small d-block", children: file.filepath }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 160,
                  columnNumber: 55
                })
              ] }, void 0, true, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 158,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("td", { children: [
                /* @__PURE__ */ jsxDEV("span", { className: "d-block", children: file.sourcecourse }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 162,
                  columnNumber: 29
                }),
                /* @__PURE__ */ jsxDEV("span", { className: "text-muted small", children: file.sourcefolder }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 162,
                  columnNumber: 81
                })
              ] }, void 0, true, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 162,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("td", { children: [
                /* @__PURE__ */ jsxDEV("span", { className: "d-block", children: file.targetcourse }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 163,
                  columnNumber: 29
                }),
                /* @__PURE__ */ jsxDEV("span", { className: "text-muted small", children: file.targetfolder }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 163,
                  columnNumber: 81
                })
              ] }, void 0, true, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 163,
                columnNumber: 25
              }),
              /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV(StatusBadge, { status: file.decision, label: file.decisionlabel }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 164,
                columnNumber: 29
              }) }, void 0, false, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 164,
                columnNumber: 25
              }),
              showsRun && /* @__PURE__ */ jsxDEV("td", { children: [
                file.included && /* @__PURE__ */ jsxDEV(StatusBadge, { status: file.state, label: file.statelabel }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 166,
                  columnNumber: 47
                }),
                file.result !== "" && /* @__PURE__ */ jsxDEV("span", { className: "text-muted small d-block", children: file.result }, void 0, false, {
                  fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                  lineNumber: 167,
                  columnNumber: 52
                })
              ] }, void 0, true, {
                fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
                lineNumber: 165,
                columnNumber: 38
              })
            ] }, file.id, true, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 149,
              columnNumber: 42
            })) }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 148,
              columnNumber: 17
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
            lineNumber: 129,
            columnNumber: 13
          }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
            lineNumber: 128,
            columnNumber: 30
          }),
          /* @__PURE__ */ jsxDEV(
            Pagination,
            {
              page,
              perpage,
              total: data?.total ?? 0,
              onPage: (next) => {
                setPage(next);
              },
              text,
              onPerPage: setPerpage
            },
            void 0,
            false,
            {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
              lineNumber: 173,
              columnNumber: 9
            }
          )
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
          lineNumber: 127,
          columnNumber: 9
        })
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FilesPanel.tsx",
      lineNumber: 92,
      columnNumber: 12
    }
  );
}, "FilesPanel");
var FilesPanel_default = FilesPanel;
export {
  FilesPanel_default as default
};
//# sourceMappingURL=FilesPanel.dev.js.map
