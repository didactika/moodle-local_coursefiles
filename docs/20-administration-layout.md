# Administration and review screens

MultiPicker opens through **Site administration → Plugins → Local plugins → MultiPicker**. Settings, Profiles, Migrations and Log share permission-aware tabs. A delegated profile manager has an authorized entry without site configuration access. The Log is also registered under **Site administration → Reports → MultiPicker material reuse**. Every route checks its own capability.

![Administration pages](diagrams/design/administration-pages.svg)

## Settings and profiles

The native settings form controls authorship, course overrides and missing authors. Strict defaults are preserved. Profile cards show status and revision; creating/editing opens a separate native form. Guided mode edits the course field/expression, family/display templates, optional edition capture, matching dimensions, category scope, public sources, publication policy, source/destination folder predicates, section matching and duplicate handling. Capture filters use repeatable capture/label/optional controls.

Guided mode starts from the **server-loaded revision**, preserves advanced settings without a form control and does not require a JSON document. Advanced mode retains the complete JSON schema, including multiple parsers and explicit section maps. A saved section map is preserved by guided edits; mapping entries are edited in advanced mode. Preview validates unsaved rules without activating a revision. Saving activates a new validated revision. Cancel discards unsaved form data.

Select several cards or Select all to **Export selected**, or use **Export all profiles**. Exports contain active configurations only, without native course files, logs, database IDs or provenance. Import accepts an exported JSON bundle or a single JSON profile through Moodle's native upload control. All documents are validated before any write. Each imported profile has a new identity; existing profiles are never overwritten. Check category IDs, explicit mappings and publication rules for the destination site. Import accepts at most 500 documents and 1 MB. This workflow does not parse YAML.

The complete conversations and design sequences are documented in [UC11: export](rup/requirements/use-cases/uc11-export-profiles.md) and [UC12: import](rup/requirements/use-cases/uc12-import-profiles.md).

## Four-step migration wizard

1. **Choose profile:** select the course grouping and folder rules.
2. **Source and destination:** choose edition or a named capture filter (such as cohort or semester), enter distinct source/destination values and confirm ordinal section layout if applicable. Matching still requires one source and one destination within each family and remaining configured dimensions.
3. **Review:** prepare every matching operation, then display only 50 per page. Search course names, folders or filenames and filter by decision. Include/exclude checkboxes affect the rendered page only; Save selection preserves other pages. Include every file and Exclude every file apply to the entire plan regardless of the current search. A configuration change creates a new preview.
4. **Execute:** open a separate wizard screen with whole-selection counts, confirmation, maintenance requirement, progress and cancellation. Files remain unchanged until authorized execution is queued.

![Migration review flow](diagrams/behavior/processes/administration-migration.svg)

Operation snapshots/decisions remain immutable. Selection changes update the review digest and are recorded in the log; stale approvals are refused. Selection locks after any run has been created. Excluding a blocked file can remove that file's block, but unresolved course/placement issues still block the plan. Execution queues **all included operations across every page**, never only the visible search results. Excluded files are not processed or counted in execution progress. Empty selections cannot queue a run. Native source/destination permissions, author policy, duplicate checks, maintenance and live revalidation remain enforced.

New plans store a compact specification and streamed operation fingerprint rather than duplicating every snapshot in JSON. Stored operations carry their inclusion state. Queueing shares the plan lock with selection changes and validates the reviewed fingerprints. A second active run is refused.

Course and placement issues have their own pages of 50 entries, independent of the file pages. Their complete set remains in the stored plan and blocks execution even when the current issue page does not display a particular problem.

## Logs and presentation

The report uses a compact action timeline with action/user/date labels and an expandable reference. Action/reference/date filters retain the descending-ID cursor on older pages. Date bounds use the reader's timezone, including the whole selected end date. Raw structured details remain undisplayed.

Shared component CSS uses theme variables, compact cards, pill labels and coloured disclosure chevrons. Native form controls, labels, fieldsets, validation and accessibility behavior remain owned by Moodle. JavaScript enhances Select all and saves changed migration page checkboxes before searching, paging, filtering or continuing to execution. Without JavaScript, use Save selection before navigation. Manual checkboxes and whole-plan actions remain usable without it. Templates remain overrideable under `local/navigation`, `local/profile`, `local/migration`, `local/catalog` and `local/log`.

References: [Moodle Forms API](https://moodledev.io/docs/5.3/apis/subsystems/form), [native form uploads](https://moodledev.io/docs/5.0/apis/subsystems/form/usage/files).
