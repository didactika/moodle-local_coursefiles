var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration. A site can have many profiles, so the list never shows more than ten: the first ones
 * by name, or the ones that match what is typed in the search above it.
 *
 * @module     local_filebank/migration/components/parameters/ProfilePicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { useDebounced } from "../../hooks/useDebounced";
import Icon from "../ui/Icon";
const ProfilePicker = /* @__PURE__ */ __name(({ profiles, chosen, locked, invalid, onPick, onSearch, text }) => {
  const [query, setQuery] = useState("");
  const followed = useDebounced(query, 300);
  const first = useRef(true);
  useEffect(() => {
    if (first.current) {
      first.current = false;
      return;
    }
    onSearch(followed.trim());
  }, [followed]);
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-picker${invalid ? " has-problem" : ""}`, "data-region": "profile-picker", children: [
    !locked && /* @__PURE__ */ jsxDEV("div", { className: "fbm-search", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-search-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: "magnifying-glass" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 46,
        columnNumber: 47
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 46,
        columnNumber: 13
      }),
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "search",
          className: "form-control rounded-pill",
          value: query,
          placeholder: text("profile_search"),
          "aria-label": text("profile_search"),
          autoComplete: "off",
          onChange: (event) => setQuery(event.target.value)
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
          lineNumber: 47,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 45,
      columnNumber: 21
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "list-group fbm-picker-list", role: "listbox", "aria-label": text("wizard_profile"), children: [
      profiles.map((profile) => /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          role: "option",
          "aria-selected": profile.id === chosen,
          className: `list-group-item list-group-item-action${profile.id === chosen ? " active" : ""}`,
          disabled: locked && profile.id !== chosen,
          onClick: () => onPick(profile),
          children: [
            profile.id === chosen && /* @__PURE__ */ jsxDEV(Icon, { name: "check", gap: true }, void 0, false, {
              fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
              lineNumber: 67,
              columnNumber: 43
            }),
            profile.name
          ]
        },
        profile.id,
        true,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
          lineNumber: 58,
          columnNumber: 40
        }
      )),
      profiles.length === 0 && /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-hint", children: text("profile_none") }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 69,
        columnNumber: 39
      })
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 57,
      columnNumber: 9
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
    lineNumber: 44,
    columnNumber: 12
  });
}, "ProfilePicker");
var ProfilePicker_default = ProfilePicker;
export {
  ProfilePicker_default as default
};
//# sourceMappingURL=ProfilePicker.dev.js.map
