var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Which screen is open, kept in the address so that a migration can be linked to and the back button of the browser works.
 *
 * @module     local_filebank/migration/hooks/useRoute
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useCallback, useEffect, useState } from "react";
const read = /* @__PURE__ */ __name((search) => {
  const query = new URLSearchParams(search);
  const planid = Number(query.get("id")) || 0;
  if (planid > 0) {
    return { mode: query.get("edit") === "1" ? "edit" : "review", planid, from: 0 };
  }
  if (query.get("new") === "1") {
    return { mode: "new", planid: 0, from: Number(query.get("from")) || 0 };
  }
  return { mode: "list", planid: 0, from: 0 };
}, "read");
const useRoute = /* @__PURE__ */ __name((initial, pageurl) => {
  const [location, setLocation] = useState(() => initial > 0 ? { mode: "review", planid: initial, from: 0 } : read(window.location.search));
  useEffect(() => {
    const follow = /* @__PURE__ */ __name(() => setLocation(read(window.location.search)), "follow");
    window.addEventListener("popstate", follow);
    return () => window.removeEventListener("popstate", follow);
  }, []);
  const go = useCallback((target, query) => {
    const address = query === "" ? pageurl : `${pageurl}${pageurl.includes("?") ? "&" : "?"}${query}`;
    window.history.pushState({}, "", address);
    setLocation(target);
    window.scrollTo({ top: 0 });
  }, [pageurl]);
  return {
    ...location,
    openList: /* @__PURE__ */ __name(() => go({ mode: "list", planid: 0, from: 0 }, ""), "openList"),
    openNew: /* @__PURE__ */ __name((from = 0) => go({ mode: "new", planid: 0, from }, from > 0 ? `new=1&from=${from}` : "new=1"), "openNew"),
    openReview: /* @__PURE__ */ __name((planid) => go({ mode: "review", planid, from: 0 }, `id=${planid}`), "openReview"),
    openEdit: /* @__PURE__ */ __name((planid) => go({ mode: "edit", planid, from: 0 }, `id=${planid}&edit=1`), "openEdit")
  };
}, "useRoute");
export {
  useRoute
};
//# sourceMappingURL=useRoute.dev.js.map
