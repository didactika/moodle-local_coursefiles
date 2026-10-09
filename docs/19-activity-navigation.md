# Entering material import

Use the native **Add files** control, then select **MultiPicker**, identified by its repository icon. In a supported activity text editor, use the native Link file browser and select the same repository. Creation and editing use the same entry; the activity does not need its first save before importing.

![Native import entry](diagrams/design/sequences/activity-entry.svg)

The Folder **Edit** content page keeps its pending draft and Save changes/Cancel controls. Other activity Settings forms retain their own saving controls. No additional import button, activity tab or settings redirect is required.

An enabled relationship profile, repository instance and authorized native owning field are required. The scope belongs to the actual actor/context/field/draft. Its default module filter comes from the destination activity; users can choose other source types. [Visual selection](17-folder-selection.md) documents filters, review, permissions and limits.
