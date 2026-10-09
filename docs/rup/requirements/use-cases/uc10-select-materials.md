# UC10 — Select files and folders together

| Attribute | Behavior |
|---|---|
| Primary actor | Teacher editing a Folder or File activity |
| Goal | Review a mixed selection and populate the current native draft |
| Preconditions | Enabled profile, authorized sources, writable native Folder/File filemanager and permitted author policy |
| Result | Copied draft files retain original declarations and relative paths; native Save/Cancel owns permanent storage |

![UC10 conversation](../../../diagrams/requirements/use-case-details/uc10-select-materials.svg)

| Step | Responsibility | Action |
|---|---|---|
| 1 | Teacher | Presses Import materials beside the supported native filemanager |
| 2 | Moodle / native_filemanager | Keeps pending fields, opens the native picker and displays authorized source cards |
| 3 | Teacher | Filters and selects files and real folders across results, then chooses a destination and folder structure |
| 4 | draft_scope / draft_import | Expands eligible descendants and reviews current limits, declarations and conflicts |
| 5 | Teacher | Checks the review and adds the approved selection |
| 6 | CourseBridge | Revalidates approval and copies transactionally into the draft without changing its main file |
| 7 | Teacher / Moodle | Saves normally or cancels without changing the activity |

Empty/oversized selections, stale approvals, changed/deleted destinations, revoked access, strict author policy, conflicting paths and incompatible native limits are explained. A rejected review leaves the selector available. Search filters do not restrict a selected folder's descendants. Source paths are preserved below the chosen destination; changing the destination or folder mode requires another review.

![UC10 design sequence](../../../diagrams/design/sequences/folder-selection.svg)

[Detailed screen contract](../../../17-folder-selection.md) · [Use-case catalogue](README.md).

File activities preserve their main-file choice: [File screen contract](../../../18-resource-selection.md).

![File-specific sequence](../../../diagrams/design/sequences/resource-selection.svg)
