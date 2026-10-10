/**
 * Typed adapter for Moodle APIs without an equivalent native ESM module.
 *
 * @module local_multipicker/compat/fragment
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { loadLegacy } from "./amd";
var fragment_default = await loadLegacy("core/fragment");
export {
  fragment_default as default
};
//# sourceMappingURL=fragment.dev.js.map
