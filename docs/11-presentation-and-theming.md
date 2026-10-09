# Presentation and theme overrides

The interface uses the active Moodle theme's page layout, typography, colours, buttons, forms, cards and tables. There is no embedded colour palette, mandatory frontend framework.

![Output boundary](diagrams/design/presentation.svg)

## Screens

The Materials page places filters before grouped file cards. Each card identifies the available course, edition, activity, relative path and author/licence. Empty results provide a clear-filters action. Catalog refresh and explicit publication appear as separate secondary sections; publication selects an activity with a declared public material provider.

Migration reports show source/destination editions, copy/skip/block counts, readable decisions and run outcomes. Destination course/section/folder names replace raw module IDs. Policy screens show the effective site/course policy and allowed overrides. Profile administration retains Moodle's native form layout.

Labels are associated with controls. Tables use column headers and responsive containers. State and decision meaning is conveyed in text; colour is not the only signal. Dynamic filenames, authors and profile labels are escaped by Mustache.

## Template contract

| Template | Responsibility |
|---|---|
| `local_multipicker/local/navigation/tabs` | Permission-aware site/course page links |
| `local_multipicker/local/migration/setup` | Selected profile, current rules and recent previews |
| `local_multipicker/local/profile/summary` | Exact relationship and migration rule descriptions |
| `local_multipicker/local/catalog/index` | Course page and secondary controls |
| `local_multipicker/local/catalog/filters` | Named GET filters |
| `local_multipicker/local/catalog/material_card` | One content group and exact origins |
| `local_multipicker/local/catalog/publication` | Named publication/revocation selector |
| `local_multipicker/local/migration/report` | Reviewed decisions and run state |
| `local_multipicker/local/policy/course` | Effective policy and allowed course selection |
| `local_multipicker/local/profile/list` | Named profiles, enabled state and active revision |
| `local_multipicker/local/log/index` | Labelled action/reference filters and paginated action records |
| `local_multipicker/local/selection/browser` | Embedded compact filters, navigable cards and checkbox selection |
| `local_multipicker/local/selection/review` | Mixed-selection paths, declarations, decisions and resulting draft totals |

Page controllers pass plain data to `output/view`. `output/renderer::render_view()` selects the component template. Services own authorization and copying; a theme changes presentation without replacing those decisions.

The [administration layout](20-administration-layout.md) separates lists, editing, migration scope and stored review pages. Reviews use grouped file lists and bounded decision pages. No custom JavaScript is needed for the page tabs or disclosure controls.

Override a template at:

```text
theme/mytheme/templates/local_multipicker/local/catalog/material_card.mustache
```

Use the same basename for another listed template. The theme may also override the component renderer using Moodle's renderer factory. Stable hooks include `.multipicker-catalog`, `.multipicker-material`, `.multipicker-migration`, `.multipicker-policy` and `data-region` attributes.

The companion repository documents its own `repository_multipicker/local/picker/search_form` override. Native picker lists, dialogs, file selection and save controls remain core presentation.

References: [Moodle theme plugin API](https://moodledev.io/docs/4.5/apis/plugintypes/theme), [Output API](https://moodledev.io/docs/5.3/apis/subsystems/output), [templates](https://moodledev.io/docs/4.5/guides/templates).


## Embedded chooser

The browser uses native cards and labelled checkboxes with primary-colour hover/focus. Source activity/type and the first two capture filters appear beside filename search; further controls use a native disclosure. Cards/List buttons change the result layout. Debounced Fragment updates preserve the search document, selection and focus. Grid cards stack on small screens.

The review is a separate embedded page with a Back arrow and collapsible folder tree. Its recursive partial is local_multipicker/local/selection/review_node. Folder arrows and Back/Cards/List icons are component assets in pix/; the repository sidebar icon belongs to repository_multipicker.

Scoped styles.css uses the active theme's primary, surface, border and radius variables, with Moodle-compatible fallbacks. .multipicker-native-host enlarges only the active embedded picker panel and hides core layout/path controls that do not operate on this chooser. A parent observer releases this styling when another repository takes over.

Theme overrides must retain form field names, [data-multipicker-panel], .multipicker-browser, data-contextid, data-multipicker-filter/count/review/confirm and data-multipicker-layout hooks. Names use context-aware format_string(), then only sanitized formatted names are rendered as HTML. Plain filenames and author declarations remain Mustache-escaped. Server authorization and native limits are independent of theme overrides.
