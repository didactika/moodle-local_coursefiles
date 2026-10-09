# Mixed selection in File settings

Open a File activity's **Settings** and press **Select several files or folders** beside its native filemanager. The same form opens the reuse section without losing pending fields. Filters, multiple choices, review and draft addition follow the [same workflow as Folder settings](17-folder-selection.md). Select published files and real source folders together; nested paths and original author/licence declarations are retained.

## Main file and supporting files

A File activity opens one main file and can store supporting files, including nested directories. Added CourseBridge files receive `sortorder = 0`: a source activity's main-file marker is not copied. Existing destination files and their main-file choice remain untouched.

Use the native filemanager's **Set main file** action when you want a different file to open. If no main file is selected, Moodle's resource validation supplies its normal default during saving. CourseBridge does not choose a main file during review or addition.

![File settings and main-file preservation](diagrams/design/sequences/resource-selection.svg)

The module-form callbacks delegate to `local/selection/module_form`. Only native `mod_folder_mod_form` and `mod_resource_mod_form` controls with a writable `files` filemanager are integrated. The actual control supplies upload limits and allowed file types; the same session-bound approval, publication checks and author policy apply to both activities. Legacy resources without the normal filemanager are excluded.

## Save and cancel

Review and addition use native no-submit actions and retain pending fields. Saved resource contents remain unchanged until Moodle saves the activity normally. Cancellation leaves the saved File activity unchanged; abandoned draft cleanup follows Moodle's lifecycle.

The repository dialog still selects one file. This integration adds no plugin JavaScript or separate transfer endpoint. It uses the standard [Moodle files-in-forms lifecycle](https://moodledev.io/docs/4.5/apis/subsystems/form/usage/files) and the exact resource forms in the required 4.5 and 5.3 checkouts.

See [UC10](rup/requirements/use-cases/uc10-select-materials.md), [native form authority](16-native-form-scope.md) and [validation evidence](07-validation-and-decisions.md).
