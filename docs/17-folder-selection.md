# Mixed selection in folder settings

Open an existing Folder activity's **Settings**, then press **Select several files or folders** beside the native filemanager. The same form opens **Reuse related materials** and jumps to that section, retaining pending fields and the current draft. CourseBridge uses native Moodle controls; ordinary saving remains in control.

1. Choose a relationship profile and optionally filter by filename or edition. **Show more** exposes course shortname, source module and file type. Press **Search materials**.
2. Use **Files and folders** to select several entries. Each entry identifies its source course and activity. Moodle's native autocomplete supports searching the displayed choices and removing selected items.
3. Press **Review selection**. Check every destination path, original author/licence and copy/skip/block decision, together with the resulting draft count and size.
4. Press **Add reviewed materials** after an unblocked review. The form reloads with the copied files in its native draft and retains pending activity fields.
5. Use Moodle's ordinary **Save and display** / **Save and return to course**, or **Cancel**. Cancelling leaves the saved activity unchanged; Moodle cleans abandoned drafts through its usual lifecycle.

The selection's native help icon explains folder expansion and retained choices. Search controls use Moodle's ordinary aligned form rows and advanced-field disclosure. The individual repository picker explains how to close its dialog and reach this same-form action.

Opening, searching and paging use native secondary buttons; review and addition use primary buttons. **More materials** appears only when another result page is available.

![Mixed-selection conversation](diagrams/requirements/use-case-details/uc10-select-materials.svg)

## Search and folder meaning

Search displays one labelled file origin per identical-content group. Filter by edition or shortname to choose another origin. Real source Folder activities and their non-empty subdirectories are distinct choices; a content group is never treated as a folder.

A selected folder includes all its eligible descendant files, including files outside the current search filters. Paths remain relative to the source activity root and merge into the destination draft root: selecting `/notes/` retains `/notes/lesson.txt`. Source activity names do not create enclosing directories. Empty directories are not transferred. There is no enclosing-folder or arbitrary destination-directory selector in this screen.

Selections survive searching and **More results**. Off-page choices use a neutral selected-item label rather than exposing stale source metadata; the review revalidates their current sources. Each page has up to 50 content groups and 150 folder choices; narrow filters if the folder-choice ceiling is reached. The synchronous batch accepts at most 200 distinct leaf files across all selected folders and files.

Overlapping selections resolve each occurrence once. Existing destination files remain intact. Same-path duplicates, conflicting content or incompatible author declarations use the profile's reviewed decisions; nothing is silently overwritten. The strict author policy blocks copies through native editors; the explicit editable policy preserves original declarations and lineage while allowing later native metadata editing.

## Native integration and authority

![Folder settings sequence](diagrams/design/sequences/folder-selection.svg)

`lib.php` delegates Moodle's `coursemodule_standard_elements` and `coursemodule_definition_after_data` callbacks to `local/selection/module_form`. Native Folder and File forms with a writable `files` filemanager and current browse/reuse/activity-management permissions are eligible. Enabled profiles are required. File activities also retain their [main-file choice](18-resource-selection.md).

`form/scoped_filemanager::read_contract()` reads the existing native element's actual options. `draft_scope` binds its actor, context, field, draft and normalized upload limits to the current session. Every action requires a session key; applying also verifies the current visible profile/selection against the stored review. Changed drafts, sources, policies, profile revisions or control limits require another review. No request can supply trusted upload limits or a different destination.

Search, paging, review and addition use registered no-submit buttons. The native form rerenders; no plugin-specific JavaScript, AJAX endpoint, core patch or frontend build is added. The review is rendered through `local_coursebridge/local/selection/review`, which themes can override.

## Supported entry points

The activity's **Reuse related materials** navigation entry opens Settings with this section expanded. It is also available when viewing the separate Folder Edit page: save or cancel changes there before opening Settings. See [activity navigation](19-activity-navigation.md).

This integration is in the Folder and File activity settings form (`/course/modedit.php`). The separate Folder **Edit** content page (`/mod/folder/edit.php`) has a different form without these module-extension callbacks. The repository modal and single-file controls continue to select one file. Other module forms are not automatically integrated.

After replacing an existing plugin installation, purge Moodle caches so its callback-discovery cache loads the new module-form callbacks. No schema migration is required for this screen.

See [scope binding](16-native-form-scope.md), [batch decisions](15-draft-batches.md), [UC10](rup/requirements/use-cases/uc10-select-materials.md) and the exact [validation record](07-validation-and-decisions.md).
