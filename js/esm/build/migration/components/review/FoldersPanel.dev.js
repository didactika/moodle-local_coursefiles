var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The folders a migration reads from and writes to. A switch puts every file of a folder in or out of the migration at once.
 *
 * @module     local_filebank/migration/components/review/FoldersPanel
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { useDebounced } from "../../hooks/useDebounced";
import { useKeptHeight } from "../../hooks/useKeptHeight";
import { PAGE_SIZES } from "../../lib/paging";
import { describeError } from "../../lib/errors";
import { getFolders } from "../../services/api";
import Alert from "../ui/Alert";
import EmptyState from "../ui/EmptyState";
import Icon from "../ui/Icon";
import Pagination from "../ui/Pagination";
import Panel from "../ui/Panel";
import Progress from "../ui/Progress";
import SearchBox from "../ui/SearchBox";
import Skeleton from "../ui/Skeleton";
import Switch from "../ui/Switch";
const FoldersPanel = /* @__PURE__ */ __name(({ migration, text, busy, onSelect }) => {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(0);
  const [perpage, setPerpage] = useState(PAGE_SIZES[0]);
  const kept = useKeptHeight();
  const [data, setData] = useState(null);
  const [error, setError] = useState("");
  const query = useDebounced(search, 250);
  useEffect(() => {
    setPage(0);
    kept.release();
  }, [query, perpage]);
  useEffect(() => {
    let current = true;
    void getFolders(migration.id, query, page, perpage).then((read) => {
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
  }, [migration.id, migration.digest, query, page, perpage]);
  const editable = migration.editable && !busy;
  return /* @__PURE__ */ jsxDEV(Panel, { title: text("tab_folders"), icon: "folder-tree", count: data?.total, region: "folders", children: [
    error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 70,
      columnNumber: 26
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-toolbar", children: /* @__PURE__ */ jsxDEV(SearchBox, { id: "fbm-folders-search", value: search, onChange: setSearch, placeholder: text("folders_search"), clearLabel: text("clear") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 72,
      columnNumber: 13
    }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 71,
      columnNumber: 9
    }),
    data === null && error === "" && /* @__PURE__ */ jsxDEV(Skeleton, { rows: 4, label: text("loading") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 74,
      columnNumber: 43
    }),
    data !== null && data.folders.length === 0 && /* @__PURE__ */ jsxDEV(EmptyState, { plain: true, icon: "folder-open", title: text("folders_empty_title"), text: text("folders_empty_text") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 75,
      columnNumber: 56
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-list-region", children: [
      data !== null && data.folders.length > 0 && /* @__PURE__ */ jsxDEV("div", { ref: kept.ref, style: kept.style, children: /* @__PURE__ */ jsxDEV("ul", { className: "fbm-folders", children: data.folders.map((folder) => /* @__PURE__ */ jsxDEV("li", { className: "fbm-folder", children: [
        /* @__PURE__ */ jsxDEV(
          Switch,
          {
            label: text("folder_switch", { name: folder.sourcefolder }),
            checked: folder.total > 0 && folder.included === folder.total,
            partial: folder.included > 0 && folder.included < folder.total,
            disabled: !editable,
            onChange: (included) => onSelect(folder, included)
          },
          void 0,
          false,
          {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 79,
            columnNumber: 17
          }
        ),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-folder-route", children: [
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-folder-side", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: folder.sourcecourse }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
              lineNumber: 88,
              columnNumber: 25
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: folder.sourcefolder }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
              lineNumber: 89,
              columnNumber: 25
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 87,
            columnNumber: 21
          }),
          /* @__PURE__ */ jsxDEV(Icon, { name: "arrow-right" }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 91,
            columnNumber: 21
          }),
          /* @__PURE__ */ jsxDEV("span", { className: "fbm-folder-side", children: [
            /* @__PURE__ */ jsxDEV("strong", { children: folder.targetcourse }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
              lineNumber: 93,
              columnNumber: 25
            }),
            /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: folder.targetfolder }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
              lineNumber: 94,
              columnNumber: 25
            })
          ] }, void 0, true, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 92,
            columnNumber: 21
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
          lineNumber: 86,
          columnNumber: 17
        }),
        /* @__PURE__ */ jsxDEV("div", { className: "fbm-folder-count", children: [
          /* @__PURE__ */ jsxDEV("span", { children: text("folder_included", { included: folder.included, total: folder.total }) }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 98,
            columnNumber: 21
          }),
          /* @__PURE__ */ jsxDEV(Progress, { value: folder.included, max: Math.max(1, folder.total), label: text("progress"), tone: "success" }, void 0, false, {
            fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
            lineNumber: 99,
            columnNumber: 21
          })
        ] }, void 0, true, {
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
          lineNumber: 97,
          columnNumber: 17
        })
      ] }, `${folder.sourcecourseid}:${folder.sourcecmid}:${folder.targetcourse}:${folder.targetfolder}`, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
        lineNumber: 78,
        columnNumber: 43
      })) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
        lineNumber: 77,
        columnNumber: 93
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
        lineNumber: 77,
        columnNumber: 54
      }),
      /* @__PURE__ */ jsxDEV(
        Pagination,
        {
          page,
          perpage,
          total: data?.total ?? 0,
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
          fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
          lineNumber: 103,
          columnNumber: 9
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
      lineNumber: 76,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/review/FoldersPanel.tsx",
    lineNumber: 69,
    columnNumber: 12
  });
}, "FoldersPanel");
var FoldersPanel_default = FoldersPanel;
export {
  FoldersPanel_default as default
};
//# sourceMappingURL=FoldersPanel.dev.js.map
