# Administration and review screens

Open Site administration > Plugins > Local plugins > MultiPicker. Settings, Profiles, Migrations and Log use native, permission-aware secondary navigation. The native MultiPicker administration category opens Settings directly for site administrators and the first permitted page for delegated managers. Each route checks its capability. Delegated profile managers have an entry without site settings access. Log also appears under Site administration > Reports > MultiPicker material reuse.

![Administration pages](diagrams/design/administration-pages.svg)

## Settings and profiles

The native settings form controls author policy, course overrides, missing-author handling and independent log, migration and profile-version retention limits. Zero disables the corresponding limit. Core help icons explain fields and repeatable row columns.

Profiles use a searchable table with grouping, capture labels, state and modification date. The name opens the editor. Native grouped icons open version history, edit, enable/disable or delete the row. Compact labelled bulk actions appear only for an explicit selection. Enable and Disable use the same native primary colour and appear only when selected states make them applicable. State changes use authenticated Fragment responses, retain checked rows and show a native success toast. Export all, Import profiles and selection controls stay above the table. Profile deletion removes the profile from normal use while retaining referenced history; active migrations protect profile changes.

The guided editor uses aligned native fields and an editable filter table with at most nine definitions. Advanced rules replace the guided grouping sections. There is no separate edition section or migration folder configuration. See [profile configuration](04-configurable-rules.md).

Import accepts an exported JSON bundle or a single profile through the native upload control. The limits are 500 profiles, 5,000 historical versions and 16 MB. Validation completes before any profile is imported; existing profiles are not overwritten. Export includes retained versions. [Version history](25-profile-version-history.md) compares configurations field by field and activates a selected existing version without duplicating it.

## Migration workflow

The Migrations tab opens a filtered history table. Native grouped actions inspect a record, edit blocked configuration or delete inactive history. Row checkboxes reveal the bulk delete icon. Queued/running records are protected; bulk deletion validates all selected records atomically and retains copied native material and original attribution. Source/destination scope, date and status distinguish entries without an internal Plan reference column.

New migration opens a wizard: choose profile, configure source/destination and folder correspondences, review, then confirm execution. Capture labels come from the profile; a profile without filters omits both filter sections. Folder correspondences belong to this migration and have a responsive editable row table. Existing-section mapping and explicit creation/overwrite choices are described in [migration behavior](06-migrations.md).

Review groups courses, folders and files into collapsible sections. Their checkboxes save automatically through authenticated core Fragment responses. Course/folder selection includes their full contents; Select all covers the entire plan, including other pages. A selection update changes only checks, group counts, approval and continuation state. File nodes and scrolling remain in place. Search and pagination do not change the queued set.

Placement conflicts offer an existing destination folder, creation inside an existing section or exclusion. Same-path content conflicts offer overwrite or the file-exclusion checkbox. Resolving a conflict prepares a new plan and preserves the previous review and saved exclusions. No native destination is created until execution. Hidden/restricted sources do not generate destination conflicts; missing sources are reported without creating empty folders.

Continue to execution is always rendered and stays disabled during saving, failed saves, unresolved blockers or an empty copy set. A ready plan opens the execution step. Pressing Execute submits the session key and current approval digest; no extra acknowledgment checkbox is required. Execution revalidates the complete included set, reports bounded-batch progress and supports cancellation without enabling site maintenance.

![Configure, review and execute](diagrams/behavior/processes/administration-migration.svg)

## Pagination, filters and actions

Profiles, profile history, migrations, Log, review files and review issues share bounded 10/25/50 sizes, a result range, page count and native Previous/Next icon groups. Review files and issues use independent page parameters. Changing page size retains filters and returns the affected page to its start. Filters on migrations and Log use a bounded top-right popover with their own Apply/Reset controls.

Navigation and selection actions sit above their content: Back is on the left, and Select all precedes the selectable rows. Save, Continue and Execute follow the content at the bottom. Row actions stay beside their row. A disabled Continue control remains visible with native disabled styling; placement alerts and resolution fields explain what needs attention. Row actions stay together in a native button group. Bulk actions are hidden until selected and use compact icons, translated labels and accessible titles. Inspection, history/export, editing, activation/deactivation and deletion use meaningful native outline colours and core icons. Footers remain in document flow and do not cover form controls. The grouping follows Didactika ServiceManager while leaving core controls and theme presentation intact.
