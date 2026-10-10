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

All matches are prepared together, but only fifty file operations and fifty placement issues are rendered per page. Search and decision filters do not limit the queued set. Collapsible course and folder groups have checkboxes that affect their entire group across every page. Individual exclusions survive searching and navigation. Whole-plan Include/Exclude actions apply to every operation. Saving selection changes the approval digest; a stale digest cannot queue work.

New migration forms skip byte-identical files even when their existing declaration differs, preserving that declaration. Legacy strict-attribution plans retain their original decisions. Overwriting captures the previous and incoming declarations in history and changes only the explicitly reviewed destination path.

## Execution and recovery

Queueing requires the reviewed digest, an active unchanged profile, execution permission, maintenance mode and an exclusive administrative editing window. Queueing and selection share the plan lock; profile lifecycle changes share the profile lock. A plan cannot have two active runs, and selection freezes after its first run.

![Native execution](diagrams/design/sequences/uc06-execute-migration.svg)

The actor-bound task processes at most twenty-five operations per slice. It rechecks live source eligibility, author policy, course identities and native destination snapshots. A stale source is refused before destination creation. Missing folders are created through add_moduleinfo; a separate binding record stores the native module and its snapshot without rewriting reviewed operation snapshots. Creation and destination locks serialize plugin writes.

Folder writes use Moodle drafts and the native folder update contract. Unrelated files, paths, visibility and completion metadata remain intact. Copies preserve source author/license and origin history. Identical native saves are recognized after interrupted execution. Created target bindings are reused; no second folder is created for the next file. A changed target blocks later operations.

![Plan states](diagrams/behavior/states/migration-plan.svg)

![Run states](diagrams/behavior/states/migration-run.svg)

Cancellation takes effect at an operation boundary. Remaining pending operations can resume under the same reviewed plan; completed effects remain. Individual failures are logged and unaffected files continue. Automatic rollback of completed course writes is unsupported. [Retention](23-history-and-retention.md) removes old plans and attempts without deleting course files or original attribution.
