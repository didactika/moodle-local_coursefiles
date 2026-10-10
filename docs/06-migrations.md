# Reviewed bulk copying

The Migrations tab opens a searchable, paginated history of prepared, queued, running, completed, partial and cancelled migrations. New migration opens a separate configuration wizard; profile administration has its own tab.

![Migration administration](diagrams/behavior/processes/administration-migration.svg)

## Scope and folder correspondences

The selected profile supplies named-capture filters for both source and destination. Several filters can be used together; blank values leave that dimension unrestricted. Selected captures are removed from the remaining pairing dimensions. Each family/dimension pair must have exactly one source and one destination. Ambiguous matches are reported and block execution rather than choosing a course automatically.

When the profile has an explicit empty filter set, source/destination filter sections are omitted. Folder correspondences then operate inside each matched course. This does not infer a direction between related courses.

Each migration has an editable table of up to twenty correspondences. Source and destination select name or idnumber, and exact, regular-expression or template matching. Named source captures can fill the target template. Sections match by reviewed ordinal, unique nonempty name, or explicit source/destination section IDs. Moodle sections have no native idnumber. Section maps are local to their course pair.

If a source folder is missing, the review reports it without creating a source or blocking unrelated files. If a destination folder is missing, the administrator chooses Block or Create. Creation requires a concrete name from an exact selector or resolved template; a destination regex alone cannot name a new activity. The reviewed target section must already exist. Creating sections and guessing ambiguous destinations are unsupported.

![Prepare reviewed intent](diagrams/design/sequences/uc05-preview-migration.svg)

## Decisions and selection

| Condition | Outcome |
|---|---|
| Missing destination path | Copy when current policy allows |
| Identical bytes | Skip; keep the existing destination declaration |
| Same path with different bytes | Block by default; overwrite only when explicitly selected |
| Same bytes at another path | Skip/report or keep, according to the migration choice |
| Missing source folder | Inform; continue with other correspondences |
| Missing destination folder | Block or create, according to the reviewed choice |
| Ambiguous course, section or destination | Block |
| Changed source, identity, placement or policy after review | Block stale operation |
| Unsupported strict author protection | Block native copy |

All matches are prepared together, but file and issue pages render at most fifty records, with shared 10/25/50 sizes. Search and decision filters do not limit the queued set. Collapsible course and folder groups have checkboxes that affect their entire group across every page. Individual exclusions survive searching and navigation. The Select all checkbox includes or excludes every operation. Automatic saving changes the approval digest; a stale digest cannot queue work.

New migration forms skip byte-identical files even when their existing declaration differs, preserving that declaration. Legacy strict-attribution plans retain their original decisions. Overwriting captures the previous and incoming declarations in history and changes only the explicitly reviewed destination path.

## Execution and recovery

Queueing requires the reviewed digest, an active unchanged profile, execution permission and explicit confirmation. The site remains available. Queueing and selection share the plan lock; profile lifecycle changes share the profile lock. A plan cannot have two active runs, and selection freezes after its first run.

![Native execution](diagrams/design/sequences/uc06-execute-migration.svg)

The actor-bound task processes at most twenty-five operations per slice. It rechecks live source eligibility, author policy, course identities and native destination snapshots. A stale source is refused before destination creation. Missing folders are created through add_moduleinfo; a separate binding record stores the native module and its snapshot without rewriting reviewed operation snapshots. Creation and destination locks serialize plugin writes.

Folder writes use per-path File API transactions and the native folder_updated event; they never save a whole destination draft. Unrelated files, paths, visibility and completion metadata remain intact. Copies preserve source author/license and origin history. Identical native saves are recognized after interrupted execution. Created target bindings are reused; no second folder is created for the next file. A changed target blocks later operations.

![Plan states](diagrams/behavior/states/migration-plan.svg)

![Run states](diagrams/behavior/states/migration-run.svg)

Cancellation takes effect at an operation boundary. Remaining pending operations can resume under the same reviewed plan; completed effects remain. Individual failures are logged and unaffected files continue. Automatic rollback of completed course writes is unsupported. [Retention](23-history-and-retention.md) removes old plans and attempts without deleting course files or original attribution.

Blocked history entries expose a native Edit icon. Editing reloads the reviewed scope, correspondences and conflict options into a new configuration; submitting creates a new immutable plan and leaves the original record available in history. Ordinary entries use clickable names and grouped native inspection/deletion icons without an internal reference column.

Destination locks serialize MultiPicker writers. Per-file transactions recheck the expected existing path immediately before replacement. Unrelated files added by a teacher after review remain present. Native editors retain Moodle's ordinary concurrent-edit behavior; MultiPicker does not claim to lock editors opened before a migration.

A prepared plan with no included copy or overwrite operations is automatically cancelled. This includes an empty source result and a selection containing only identical-file skips. It creates no background run and does not create an empty destination folder. Before its first run, including actual work can make the plan reviewable again. Unresolved blockers still take priority.

Inactive migrations can be deleted from the history table. The capability- and session-key-checked action shares the execution lock, refuses queued or running attempts, and deletes target bindings, operations, run records and the plan. Previously copied native files and original-author records remain unchanged. Deletion is logged.

## Automatic selection and conflict resolution

Review checkboxes save automatically through Moodle core Fragment. Course and folder controls apply to all their operations; Select all applies to the complete plan, including other pages and files outside the current search. A selection response contains only checked IDs, group counts, the new approval digest and continuation state. Existing file nodes, disclosure state and scrolling remain in place. The unenhanced POST form remains available when JavaScript is unavailable.

Continue to execution is always rendered. It is disabled while a change is pending, after a failed save, when no copy remains, or while included blockers remain. Execution still requires a separate confirmation and revalidates the complete included set on the server.

Placement conflicts offer an existing destination folder, creation inside an existing destination section, or explicit exclusion. File-content conflicts offer overwrite or exclusion through the file checkbox. Resolution creates a new plan under the original selection/queue lock, retains the previous review and preserves exclusions by source occurrence and destination course. It never creates a native folder during review. Duplicate creation names remain blocked; existing folders must be chosen explicitly. Missing sources are reported without creating empty sources, and hidden or restricted source placements do not generate destination conflicts.

History has grouped native View, Edit and Delete icons, a current-page multiselect and a destructive bulk action shown only when selected. Bulk deletion locks every selected plan, validates the complete selection before deleting, and refuses queued/running work atomically. Native course files and attribution are retained.

Profiles, profile history, migrations, review files, review issues and Log share range/page summaries, Previous/Next icons and 10/25/50 page sizes. Review file and issue pages remain independent; rendering is bounded while preparation and queueing cover every matching included operation.
