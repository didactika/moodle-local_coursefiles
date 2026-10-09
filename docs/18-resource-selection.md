# Supporting files in File activities

A File activity's **Settings** form exposes its ordinary native Add files control; choose **MultiPicker** in that picker. The [embedded material browser](17-folder-selection.md) supports mixed file/folder selections without leaving that pending form. New File activities can use the same draft workflow before saving.

Imported supporting files use `sortorder = 0`. The destination's existing main-file marker remains unchanged; selecting a different main file stays Moodle's responsibility. A source resource's main-file marker is never inherited by a copied supporting file.

![Resource main-file sequence](diagrams/design/sequences/resource-selection.svg)

The actual native control supplies file types and upload limits. Review and transfer recheck publication, ownership, permissions, current author policy and the complete draft. Normal Save writes permanent files; Cancel leaves the saved resource unchanged. Controls without an authorized writable native draft retain individual selection. Other registered activity filemanagers/editors use their own native limits and permission contracts.

This native Settings workflow requires activity-management permission. The lower `mod/folder:managefiles` permission applies exclusively to the separate Folder content editor. See [permissions and scope](16-native-form-scope.md).
