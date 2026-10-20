var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Keep the height of a list when it goes to another page, so a last page with fewer rows does not make the page jump; a new
 * search or filter lets it take its own height again.
 *
 * @module     local_filebank/migration/hooks/useKeptHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useRef, useState } from "react";
const useKeptHeight = /* @__PURE__ */ __name(() => {
  const ref = useRef(null);
  const [height, setHeight] = useState(0);
  return {
    ref,
    style: height > 0 ? { minHeight: `${height}px` } : void 0,
    keep: /* @__PURE__ */ __name(() => setHeight((current) => Math.max(current, ref.current?.offsetHeight ?? 0)), "keep"),
    release: /* @__PURE__ */ __name(() => setHeight(0), "release")
  };
}, "useKeptHeight");
export {
  useKeptHeight
};
//# sourceMappingURL=useKeptHeight.dev.js.map
