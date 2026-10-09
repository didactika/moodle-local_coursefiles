# Entering material import

CourseBridge exposes **Import materials** beside supported native Folder/File filemanagers. Pressing it opens the existing Moodle picker and selects **Materials from related courses**. Users can also use the normal Add file action and select that repository themselves.

There is no additional activity navigation tab or redirect to another Settings form. The separate Folder **Edit** content page keeps its own pending draft and normal Save changes/Cancel controls. File and Folder Settings retain their own Save/Cancel controls. Newly created activities do not need to be saved before importing.

![Native import entry](diagrams/design/sequences/activity-entry.svg)

An enabled relationship profile and enabled CourseBridge repository are required. The owning native field is registered only on supported routes and only for an authorized editor. The same scope powers the shortcut and normal repository entry. [Folder workflow](17-folder-selection.md) documents selection, filters, permissions and limits.
