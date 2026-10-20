var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the window that starts one and the page of each one, all in one application that
 * never leaves the page. The server works everything out; this only asks and shows.
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
import NewMigrationModal from "./migration/components/wizard/NewMigrationModal";
import { useRoute } from "./migration/hooks/useRoute";
import { describeError } from "./migration/lib/errors";
import { createText } from "./migration/lib/text";
import { getOptions } from "./migration/services/api";
import Alert from "./migration/components/ui/Alert";
function Migration(props) {
  const text = useMemo(() => createText(props.strings), [props.strings]);
  const route = useRoute(props.planid, props.pageurl);
  const [options, setOptions] = useState(null);
  const [error, setError] = useState("");
  const [creating, setCreating] = useState(false);
  const [initial, setInitial] = useState(null);
  useEffect(() => {
    document.querySelectorAll(".fbm-boot").forEach((block) => block.remove());
    getOptions().then(setOptions).catch((problem) => setError(describeError(problem)));
  }, []);
  const openWizard = /* @__PURE__ */ __name((parameters) => {
    setInitial(parameters);
    setCreating(true);
  }, "openWizard");
  return /* @__PURE__ */ jsxDEV("div", { className: "filebank-migration", "data-region": "fbm-app", children: [
    error !== "" && /* @__PURE__ */ jsxDEV(Alert, { tone: "danger", live: true, children: error }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/Migration.tsx",
      lineNumber: 52,
      columnNumber: 26
    }, this),
    route.planid > 0 ? /* @__PURE__ */ jsxDEV(
      Review,
      {
        id: route.planid,
        text,
        options,
        onBack: () => route.open(0),
        onNew: openWizard
      },
      route.planid,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/Migration.tsx",
        lineNumber: 54,
        columnNumber: 15
      },
      this
    ) : /* @__PURE__ */ jsxDEV(
      MigrationList,
      {
        text,
        pagesizes: props.pagesizes,
        onOpen: route.open,
        onNew: () => openWizard(null)
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/Migration.tsx",
        lineNumber: 62,
        columnNumber: 15
      },
      this
    ),
    /* @__PURE__ */ jsxDEV(
      NewMigrationModal,
      {
        open: creating,
        options,
        initial,
        text,
        onClose: () => setCreating(false),
        onCreated: (id) => {
          setCreating(false);
          route.open(id);
        }
      },
      void 0,
      false,
      {
        fileName: "public/local/filebank/js/esm/src/Migration.tsx",
        lineNumber: 68,
        columnNumber: 9
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/Migration.tsx",
    lineNumber: 51,
    columnNumber: 12
  }, this);
}
__name(Migration, "Migration");
export {
  Migration as default
};
//# sourceMappingURL=Migration.dev.js.map
