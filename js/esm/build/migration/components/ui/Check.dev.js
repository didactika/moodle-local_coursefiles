var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A check box that can also say that some of what it covers is checked.
 *
 * @module     local_filebank/migration/components/ui/Check
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef } from "react";
const Check = /* @__PURE__ */ __name(({ checked, partial = false, label, onChange, disabled = false }) => {
  const input = useRef(null);
  useEffect(() => {
    if (input.current) {
      input.current.indeterminate = partial && !checked;
    }
  }, [partial, checked]);
  return /* @__PURE__ */ jsxDEV(
    "input",
    {
      ref: input,
      type: "checkbox",
      className: "fbm-check",
      checked,
      disabled,
      "aria-label": label,
      title: label,
      onChange: (event) => onChange(event.target.checked)
    },
    void 0,
    false,
    {
      fileName: "public/local/filebank/js/esm/src/migration/components/ui/Check.tsx",
      lineNumber: 31,
      columnNumber: 12
    }
  );
}, "Check");
var Check_default = Check;
export {
  Check_default as default
};
//# sourceMappingURL=Check.dev.js.map
