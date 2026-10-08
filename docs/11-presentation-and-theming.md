# Presentation and theme overrides

The interface uses the active Moodle theme's page layout, typography, colours, buttons, forms, cards and tables. There is no embedded colour palette, custom JavaScript application or mandatory frontend framework.

![Output boundary](diagrams/design/presentation.svg)

## Screens

The Materials page places filters before grouped file cards. Each card identifies the available course, edition, activity, relative path and author/licence. Empty results provide a clear-filters action. Catalog refresh and explicit publication appear as separate secondary sections; publication selects a named folder/resource.

Migration reports show source/destination editions, copy/skip/block counts, readable decisions and run outcomes. Destination course/section/folder names replace raw module IDs. Policy screens show the effective site/course policy and allowed overrides. Profile administration retains Moodle's native form layout.

Labels are associated with controls. Tables use column headers and responsive containers. State and decision meaning is conveyed in text; colour is not the only signal. Dynamic filenames, authors and profile labels are escaped by Mustache.

## Template contract

| Template | Responsibility |
|---|---|
| `local_coursebridge/catalog` | Course page and secondary controls |
| `local_coursebridge/catalog_filters` | Named GET filters |
| `local_coursebridge/material_card` | One content group and exact origins |
| `local_coursebridge/publication` | Named publication/revocation selector |
| `local_coursebridge/migration_report` | Reviewed decisions and run state |
| `local_coursebridge/policy` | Effective policy and allowed course selection |
| `local_coursebridge/profile_list` | Named profiles, enabled state and active revision |
| `local_coursebridge/log` | Labelled action/reference filters and paginated action records |

Page controllers pass plain data to `output/view`. `output/renderer::render_view()` selects the component template. Services own authorization and copying; a theme changes presentation without replacing those decisions.

Override a template at:

```text
theme/mytheme/templates/local_coursebridge/material_card.mustache
```

Use the same basename for another listed template. The theme may also override the component renderer using Moodle's renderer factory. Stable hooks include `.coursebridge-catalog`, `.coursebridge-material`, `.coursebridge-migration`, `.coursebridge-policy` and `data-region` attributes.

The companion repository documents its own `repository_coursebridge/search_form` override. Native picker lists, dialogs, file selection and save controls remain core presentation.

References: [Moodle theme plugin API](https://moodledev.io/docs/4.5/apis/plugintypes/theme), [Output API](https://moodledev.io/docs/5.3/apis/subsystems/output), [templates](https://moodledev.io/docs/4.5/guides/templates).
