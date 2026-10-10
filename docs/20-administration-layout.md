# Administration and review screens

Open Site administration > Plugins > Local plugins > MultiPicker. Settings, Profiles, Migrations and Log are native, permission-aware secondary navigation. Each route checks its capability. Delegated profile managers have an entry without site settings access. Log also appears under Site administration > Reports > MultiPicker material reuse.

![Administration pages](diagrams/design/administration-pages.svg)

## Settings and profiles

The native settings form controls author policy, course overrides, missing-author handling and independent log/migration retention periods. Zero disables the corresponding expiration.

Profiles use a searchable table of twenty-five rows per page. Name opens the editor; status, grouping key, capture labels, revision and date help distinguish profiles. Native right-hand actions edit, enable, disable or delete a row. Bulk selection enables, disables, deletes or exports selected profiles; Export all and Import profiles are separate actions. Deletion removes the profile from normal use while retaining referenced revisions/history. Active migrations prevent a profile lifecycle change.

The guided editor uses ordinary fields and an editable filter table. Advanced rules replace the guided sections. There is no separate edition section or migration folder configuration. See [profile configuration](04-configurable-rules.md).

Import accepts an exported JSON bundle or a single profile through the native upload form, with limits of 500 documents and 1 MB. Validation completes before writes; each imported profile has a new identity. Native files, logs, database IDs and author history are not exported. Category and section IDs in legacy advanced documents remain site-specific.

## Migrations

The initial table shows migration reference, profile, status, date and an Open action, with twenty-five rows per page. Search and a top-right Filters control narrow the history. New migration opens the wizard:

1. Choose an enabled grouping profile.
2. Configure its source/destination capture values and editable folder correspondences. Without captures, only correspondences and placement choices appear.
3. Review collapsible course/folder groups and fifty-file pages; search, decision filters and persistent checkboxes choose the complete included set. Change configuration opens a new review without altering the stored plan.
4. Execute the approved whole set under maintenance mode; follow progress and cancel at a safe boundary.

![Review and execution](diagrams/behavior/processes/administration-migration.svg)

Course/folder actions include their off-page files. Whole-plan actions cover every matching operation. Individual exclusions remain after searching and navigation. JavaScript saves changed file checks before paging or moving to execution; without it, Save selection persists those changes. Informational missing-source notices do not block unrelated files. Ambiguous placement and included blocking decisions still prevent execution.

## Logs and native presentation

Log uses a compact table and top-right Filters disclosure for action, reference and date bounds. Pagination uses a descending-ID cursor. Date bounds use the reader's timezone and include the whole final day. Structured internal details stay undisplayed.

The PHP navigation delegates rendering to each exact core: Moodle 4.5 uses core/moremenu; the Moodle 5.3 distribution uses core/secondarymoremenu and its native React navigation. Forms, buttons, tables, icon rendering and validation remain owned by Moodle/the active theme. Custom disclosures use core chevrons. Templates remain overrideable under local/navigation, local/profile, local/migration, local/catalog and local/log.
