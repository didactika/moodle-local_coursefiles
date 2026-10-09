# Activity navigation to mixed selection

Folder and File activities expose **Reuse related materials** in their native activity administration navigation. The theme may place this entry under **More**. It opens the activity's existing Settings form with the reuse section expanded and a fragment pointing to that section.

From a Folder's separate **Edit** content page, save or cancel changes before selecting this navigation entry. Settings opens its own native editing form; unsaved values from another page are not transferred. Once in Settings, review and add several files/folders to that form's current draft, then save or cancel normally.

![Activity navigation route](diagrams/design/sequences/activity-entry.svg)

`lib.php` delegates `extend_settings_navigation` to `local/selection/activity_navigation`. The entry requires a current Folder/File module context, activity-management permission, CourseBridge browse/reuse capabilities and at least one enabled profile. The node is added only once under `modulesettings`. It uses `/course/modedit.php?update=<cmid>&return=1&coursebridge=1#id_cbheader`.

The GET flag only expands the existing reuse section. It does not submit a selection, approve a review or save an activity. The native controller and existing form eligibility checks remain authoritative. Copy actions still require the session key, an actor-bound draft scope and a current reviewed selection.

The separate `/mod/folder/edit.php` form is unchanged. Both required core checkouts use `mod_folder_edit_form`, which has no course-module form-extension callbacks. This integration provides navigation to the supported Settings workflow; it does not inject batch controls into that separate form.

The shared implementation uses the [Moodle Navigation API](https://moodledev.io/docs/4.5/apis/core/navigation), checked against both required core checkouts. The local callback is present in 4.5's `lib/navigationlib.php` and 5.3's `lib/classes/navigation/settings_navigation.php`.

See [Folder selection](17-folder-selection.md), [File selection](18-resource-selection.md) and [validation](07-validation-and-decisions.md).
