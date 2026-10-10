var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * Native Moodle 5.3 React layout selector with theme-overridable markup.
 *
 * @module local_multipicker/ViewSelector
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState } from "react";
import { normalizeLayout } from "./layout";
function ViewSelector({ initialLayout, views }) {
  const [active, setActive] = useState(normalizeLayout(initialLayout));
  return /* @__PURE__ */ jsxDEV(Fragment, { children: views.map((view) => /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: `btn btn-sm btn-outline-secondary${active === view.value ? " active" : ""}`,
      "data-multipicker-layout": view.value,
      "data-multipicker-react-view": "true",
      "aria-pressed": active === view.value,
      title: view.label,
      onClick: () => setActive(view.value),
      children: [
        /* @__PURE__ */ jsxDEV("span", { dangerouslySetInnerHTML: { __html: view.icon } }, void 0, false, {
          fileName: "public/local/multipicker/js/esm/src/ViewSelector.tsx",
          lineNumber: 39,
          columnNumber: 9
        }, this),
        /* @__PURE__ */ jsxDEV("span", { className: "multipicker-visually-hidden", children: view.label }, void 0, false, {
          fileName: "public/local/multipicker/js/esm/src/ViewSelector.tsx",
          lineNumber: 40,
          columnNumber: 9
        }, this)
      ]
    },
    view.value,
    true,
    {
      fileName: "public/local/multipicker/js/esm/src/ViewSelector.tsx",
      lineNumber: 29,
      columnNumber: 33
    },
    this
  )) }, void 0, false, {
    fileName: "public/local/multipicker/js/esm/src/ViewSelector.tsx",
    lineNumber: 29,
    columnNumber: 12
  }, this);
}
__name(ViewSelector, "ViewSelector");
export {
  ViewSelector as default
};
//# sourceMappingURL=ViewSelector.dev.js.map
