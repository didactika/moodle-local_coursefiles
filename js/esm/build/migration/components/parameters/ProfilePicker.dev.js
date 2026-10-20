var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The choice of the profile of a migration: a search and the profiles as cards to choose from, each with what it recognises. A site
 * can have many profiles, so never more than ten are shown: the first ones by name, or the ones that match the search.
 *
 * @module     local_filebank/migration/components/parameters/ProfilePicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { useDebounced } from "../../hooks/useDebounced";
import Icon from "../ui/Icon";
const SHOWN = 10;
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
  const shown = locked ? profiles.filter((profile) => profile.id === chosen) : profiles;
  return /* @__PURE__ */ jsxDEV("div", { className: `fbm-picker${invalid ? " has-problem" : ""}`, "data-region": "profile-picker", children: [
    !locked && /* @__PURE__ */ jsxDEV("div", { className: "fbm-search", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-search-icon", children: /* @__PURE__ */ jsxDEV(Icon, { name: "magnifying-glass" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 51,
        columnNumber: 47
      }) }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 51,
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
          lineNumber: 52,
          columnNumber: 13
        }
      )
    ] }, void 0, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 50,
      columnNumber: 21
    }),
    /* @__PURE__ */ jsxDEV("div", { className: "fbm-choices fbm-profile-choices", role: "radiogroup", "aria-label": text("wizard_profile"), children: shown.map((profile) => /* @__PURE__ */ jsxDEV("label", { className: `fbm-choice${profile.id === chosen ? " is-on" : ""}`, children: [
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "radio",
          name: "fbm-profile",
          value: profile.id,
          checked: profile.id === chosen,
          disabled: locked,
          onChange: () => onPick(profile)
        },
        void 0,
        false,
        {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
          lineNumber: 64,
          columnNumber: 17
        }
      ),
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-dot", "aria-hidden": "true" }, void 0, false, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 72,
        columnNumber: 17
      }),
      /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-text", children: [
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-title", children: profile.name }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
          lineNumber: 74,
          columnNumber: 21
        }),
        /* @__PURE__ */ jsxDEV("span", { className: "fbm-choice-hint", children: profile.dimensions.length > 0 ? profile.dimensions.map((dimension) => dimension.label).join(" \xB7 ") : text("profile_no_filters") }, void 0, false, {
          fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
          lineNumber: 75,
          columnNumber: 21
        })
      ] }, void 0, true, {
        fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
        lineNumber: 73,
        columnNumber: 17
      })
    ] }, profile.id, true, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 63,
      columnNumber: 37
    })) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 62,
      columnNumber: 9
    }),
    shown.length === 0 && /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-hint", children: text("profile_none") }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 83,
      columnNumber: 32
    }),
    !locked && shown.length >= SHOWN && /* @__PURE__ */ jsxDEV("p", { className: "text-muted fbm-hint", children: text("profile_more", { n: SHOWN }) }, void 0, false, {
      fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
      lineNumber: 84,
      columnNumber: 46
    })
  ] }, void 0, true, {
    fileName: "public/local/filebank/js/esm/src/migration/components/parameters/ProfilePicker.tsx",
    lineNumber: 49,
    columnNumber: 12
  });
}, "ProfilePicker");
var ProfilePicker_default = ProfilePicker;
export {
  ProfilePicker_default as default
};
//# sourceMappingURL=ProfilePicker.dev.js.map
