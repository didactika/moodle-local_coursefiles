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
const useRoute = /* @__PURE__ */ __name((initial, pageurl) => {
  const [planid, setPlanid] = useState(initial);
  useEffect(() => {
    const follow = /* @__PURE__ */ __name(() => setPlanid(Number(new URLSearchParams(window.location.search).get("id")) || 0), "follow");
    window.addEventListener("popstate", follow);
    return () => window.removeEventListener("popstate", follow);
  }, []);
  const open = useCallback((target) => {
    const address = target > 0 ? `${pageurl}${pageurl.includes("?") ? "&" : "?"}id=${target}` : pageurl;
    window.history.pushState({}, "", address);
    setPlanid(target);
    window.scrollTo({ top: 0 });
  }, [pageurl]);
  return { planid, open };
}, "useRoute");
export {
  useRoute
};
//# sourceMappingURL=useRoute.dev.js.map
