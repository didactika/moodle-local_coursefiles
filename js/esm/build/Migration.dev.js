var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the page that starts one or changes its parameters, and the page of each one, all
 * in one application that never leaves the page. The server works everything out; this only asks and shows.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_filebank/Migration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useMemo, useState } from "react";
import MigrationList from "./migration/components/list/MigrationList";
import Review from "./migration/components/review/Review";
import Alert from "./migration/components/ui/Alert";
import MigrationWizard from "./migration/components/wizard/MigrationWizard";
import { useRoute } from "./migration/hooks/useRoute";
import { describeError } from "./migration/lib/errors";
import { createText } from "./migration/lib/text";
import { getOptions } from "./migration/services/api";
function Migration(props) {
  const text = useMemo(() => createText(props.strings), [props.strings]);
  const route = useRoute(props.planid, props.pageurl);
  const [options, setOptions] = useState(null);
  const [error, setError] = useState("");
  useEffect(() => {
    document.querySelectorAll(".fbm-boot").forEach((block) => block.remove());
    getOptions().then(setOptions).catch((problem) => setError(describeError(problem)));
  }, []);
  let screen;
  if (route.mode === "new" || route.mode === "edit") {
    screen = /* @__PURE__ */ jsxDEV(
      MigrationWizard,
      {
        mode: route.mode,
        options,
        migrationId: route.mode === "edit" ? route.planid : route.from,
        text,
        onClose: () => route.mode === "edit" ? route.openReview(route.planid) : route.openList(),
        onDone: route.openReview
      },
      `${route.mode}-${route.planid}-${route.from}`,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/Migration.tsx",
        lineNumber: 46,
        columnNumber: 18
      },
      this
    );
  } else if (route.mode === "review") {
    screen = /* @__PURE__ */ jsxDEV(
      Review,
      {
        id: route.planid,
        text,
        onBack: route.openList,
        onEdit: () => route.openEdit(route.planid),
        onNew: () => route.openNew(route.planid)
      },
      route.planid,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/Migration.tsx",
        lineNumber: 56,
        columnNumber: 18
      },
      this
    );
  } else {
    screen = /* @__PURE__ */ jsxDEV(MigrationList, { text, pagesizes: props.pagesizes, onOpen: route.openReview, onNew: () => route.openNew() }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/Migration.tsx",
      lineNumber: 65,
      columnNumber: 18
    }, this);
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "filebank-migration", "data-region": "fbm-app", children: [
    error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/Migration.tsx",
      lineNumber: 69,
      columnNumber: 26
    }, this),
    screen
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/Migration.tsx",
    lineNumber: 68,
    columnNumber: 12
  }, this);
}
__name(Migration, "Migration");
export {
  Migration as default
};
//# sourceMappingURL=Migration.dev.js.map
