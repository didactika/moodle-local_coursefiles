# Visual folder and file selection

Press **Import materials** beside a Folder activity's native filemanager, or open **Materials from related courses** through its Add file button. The material browser opens inside Moodle's existing picker. It also works in the separate **Edit** content form; opening activity Settings is unnecessary.

1. Enter an optional filename or edition. **More filters and copy options** contains course shortname, source activity, file type, destination directory and folder structure. A Collection selector appears there only when several relationship profiles are enabled.
2. Tick file or folder cards. **Open folder** browses its children; **Back** returns to the parent. Choices survive navigation, filtering and pagination. **Select this page** and **Clear selection** act on the bounded selection.
3. Press **Review selection** to see destination paths, original author/licence, copy/skip/block decisions and resulting draft totals. Review creates no file copies.
4. Press **Import reviewed materials** after an unblocked review. Moodle closes the picker and refreshes the original native filemanager. Pending activity fields remain in the same form.
5. Save or cancel using Moodle's ordinary buttons. Cancelling leaves permanent activity content unchanged; abandoned drafts follow Moodle's normal cleanup.

![Mixed-selection conversation](diagrams/requirements/use-case-details/uc10-select-materials.svg)

## Folder structure and duplicates

Selecting a folder includes eligible descendant files, including descendants outside the visible search filters. Empty directories are not transferred. At most 200 source occurrences are resolved; overlapping parent/child/file selections include each occurrence once.

| Folder structure | Selected `/notes/` containing `/notes/week1/lesson.txt` |
|---|---|
| Keep original paths | `/notes/week1/lesson.txt` |
| Include the selected folder | `/notes/week1/lesson.txt` |
| Copy only its contents | `/week1/lesson.txt` |

Selecting an entire source activity with **Include the selected folder** uses its cleaned activity name as the enclosing directory. The widest selected parent determines overlapping folder placement. Existing draft destination directories prefix these relative paths. Changing the destination, mode, profile or selected items invalidates approval. A deleted destination, changed source or changed draft requires another review.

File results group identical content and retain an authorized source declaration. Edition/shortname filters choose the desired origin. Folder cards describe real source activities/directories, never synthetic content groups. Identical files are skipped according to profile rules; conflicting bytes or declarations block copying instead of overwriting.

## Native integration and permissions

On supported controller routes, the `after_config` hook registers `form/native_filemanager` using Moodle's public QuickForm element factory. It inherits the native filemanager and delegates its presentation and normal save/cancel behavior. Only `files_filemanager` on `/mod/folder/edit.php` and `files` on Folder/File `/course/modedit.php` bind a material browser. Subdirectory Moodle installations are supported. Newly created activities bind the native draft before their first save.

The live field supplies context, draft ownership, file types and count/byte/directory limits. A session-bound token connects that actual field to the embedded `/local/coursebridge/selection/index.php` page. Browser requests cannot provide their own upload limits or destination draft. Repository transfer uses Moodle's documented external chooser callback and existing protected download endpoint.

Folder content editors require `mod/folder:managefiles`, `local/coursebridge:browse`, `local/coursebridge:reuse` and repository access. They do not require `moodle/course:manageactivities`. Folder/File Settings and new activities retain their native activity-management permission. Custom roles must receive the CourseBridge capabilities explicitly; existing grants/prohibitions are preserved.

![Native picker and draft sequence](diagrams/design/sequences/folder-selection.svg)

Other modules and single-file controls keep their ordinary individual picker. Custom themes may override the browser and review templates. Strict author protection still blocks unsupported native copies; explicitly choosing editable-with-provenance retains original attribution while allowing later native metadata edits. See [author policy](09-authorship-policy.md), [scope binding](16-native-form-scope.md), [File main-file behavior](18-resource-selection.md) and [validation](07-validation-and-decisions.md).
