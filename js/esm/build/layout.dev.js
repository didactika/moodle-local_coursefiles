var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * Typed layout contract shared with the native browser event adapter.
 *
 * @module local_multipicker/layout
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const normalizeLayout = /* @__PURE__ */ __name((value) => value === "list" || value === "tree" ? value : "grid", "normalizeLayout");
export {
  normalizeLayout
};
//# sourceMappingURL=layout.dev.js.map
