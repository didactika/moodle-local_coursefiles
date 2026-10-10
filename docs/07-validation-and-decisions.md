# Validation record

Verified on **2026-10-10**, package version **2026101000**. PHPUnit and Behat use independent databases and dataroots. The main localhost installations retain their existing demo courses, files and owner-selected editable-with-provenance policy. These checks do not reset the main sites.

| Exact core | Working branch | PHP / database | Combined plugin PHPUnit | Native Firefox / Boost |
|---|---|---|---|---|
| Moodle 4.5.13, build 20260810, core 8cbae18a2898cfd8266ec91ac206e12004f0ff5f | feat/multipicker-workflows-45 | PHP 8.3.33 / PostgreSQL 17.10 | 136 tests / 617 assertions passed | 32 scenarios / 472 steps passed in three completed batches |
| Moodle 5.3, build 20261005, core 42622298fe06f9626d988d60b2bf589bd8f850e8 | feat/multipicker-workflows-5 | PHP 8.3.33 / PostgreSQL 17.10 | 136 tests / 618 assertions passed | 32 scenarios / 472 steps passed in three completed batches |

The final native coverage comprises administration/pagination (14 scenarios / 246 steps), local picker/editor workflows (15 / 189), and repository workflows (3 / 37), all passing on both exact targets. Pagination checks change sizes, move between pages, retain off-page inclusions, cancel an empty selection and confirm that selection-only Ajax keeps the same visible file node. A blocked review retains a disabled Continue control, resolves an existing destination in place and reaches execution. Profile creation/editing, responsive correspondence rows, help icons, version comparison/restoration and grouped bulk controls are exercised through native forms.

An interrupted earlier Selenium session was restarted before the final 5.3 runs. Earlier visual runs identified hidden footer buttons and sticky actions covering inputs; native fieldset closure and ordinary document-flow footers correct both. The Docker timeout factor is three; it changes waiting time only. These completed batches do not represent a single combined invocation.

PHPUnit 11 coverage attributes avoid PHPDoc metadata deprecations on the 5.3 line; PHPUnit 9 uses annotations on 4.5. Other runtimes, releases, themes and browsers remain outside the completed local matrix. The GitHub matrix is configured but has not run against published repositories.

## Native picker

Firefox enters through the actual Add files action or Tiny Link browser and repository sidebar. Tests cover existing Folder content editing, File settings, URL editors, new activities, native Save/Cancel, main-file preservation and content-only teachers without activity-settings permission. Public Assignment handouts and Tiny multi-link save/reopen are covered; private submissions remain excluded.

The configured repository name appears in both sidebar and embedded heading. Select all expands eligible recursive files; Clear selection clears actual file checks. A 61-file service fixture verifies selection across multiple pages. Parent/child selection, partial checks, overlapping roots and resulting-file tree review are exercised. The pencil opens a native rename dialog and the renamed file is imported without changing its source declaration.

A delayed real Fragment response checks continued typing, retained filter focus and stable modal dimensions. Folder selection verifies that every existing card node stays connected and results scroll is unchanged. Filters occupy at most three equal-width columns; profiles accept at most nine named-capture filters.

Service checks cover unrelated contexts, expired scopes, session keys, replay, stale sources/profiles/drafts, native type/count/byte/directory limits and transactional rollback.

## Administration and migrations

Native tests cover settings/navigation, guided editing, unsaved rule preview, portable profile upload, paginated review, persistent exclusions, execution discovery and the native Reports entry. A Spanish form creates a blank profile, checks aligned input positions, saves it and edits its name. A blocked migration reloads its scope/correspondences, selects destination creation and creates a new review while retaining both plans.

Translated row schemas live on escaped native field attributes. AMD initialization passes only the field ID. Native migration forms pass without an oversized js_call_amd warning. Fields have Moodle help icons; empty profiles do not inherit example values. Page actions remain below content and row actions use native button groups.

Service checks cover selected/all export, atomic imports, soft deletion, enable/disable, disabled-profile refusal, reviewed destination creation, explicit overwrite, interrupted/idempotent execution, stale sources, missing-source reporting, identical bytes with different declarations and retention preserving active runs.

Native bulk-action checks verify initial hiding, selection-driven visibility, icon-only content, translated tooltips/accessibility labels, clearing the last row and real disable/enable submissions. Migration tests use automatic file/course/folder selection, a whole-plan Select all checkbox, persistent Continue and explicit in-place destination resolution. Catalogue regressions verify unchanged-rule metadata reuse, immediate publication restrictions, activation-triggered rebuilding, obsolete revision refusal and successor slices beyond 25 courses.

All migration tests disable maintenance. One regression adds an unrelated teacher file after review, then queues and completes the migration while retaining its bytes and declaration. Native per-path File API writes preserve unrelated destination files. Selection crosses 50-operation pages; searches affect presentation while queueing uses the complete included set.

## Builds, installation and source checks

Captures were inspected at 1366 px and Firefox's effective 500 px minimum width. Embedded panels have no horizontal overflow at those sizes. Behat Boost CSS is regenerated after style changes. These checks do not certify every theme, viewport, keyboard workflow or screen reader.

The 4.5 navigation uses core/moremenu; the 5.3 adapter uses core/secondarymoremenu and core React navigation. Six AMD modules pass each exact core's Grunt ESLint/Rollup build; 5.3 also generates declarations. No plugin-owned TSX replacement is necessary for this native integration. Both plugins use the supplied icon and core action icons. Templates and scoped CSS remain theme-overridable.

Both cores install the fourteen-table XMLDB schema and both main sites upgrade to 2026101000 without resetting existing material. Rename/upgrade checks retain legacy records, policies, repository IDs and explicit capability prohibitions. Parent UUID has no invalid empty CHAR default; operation groups, bindings and retention indexes have upgrade coverage.

Both complete distributions, including tests, pass Moodle coding standards without errors or warnings. PHP syntax, XMLDB XSD, template language identifiers, Markdown links, UML preview siblings and Git whitespace checks pass. Workflow YAML and matrix shell syntax are checked; actual remote CI remains unverified. Cache purges expose current strings and assets on both main sites.

## Practical boundaries

Interactive batches resolve at most 200 source occurrences, including selected descendants; oversized Select all requests are refused rather than truncated. Empty directories are not transferred. Tiny Link supports multiple links; image/media subpickers retain native single-file contracts. Unregistered controls retain native behavior. Only declared public providers are searchable.

Strict policy refuses unsupported later author protection. Editable-with-provenance retains the original declaration while permitting later native edits. Migrations run without maintenance, with explicit creation/overwrite decisions. Native editors retain Moodle's ordinary concurrent-edit behavior. Retention removes neither native files nor original attribution. Backup restore does not remap plugin lineage.


## Profile history and selection refinements

Service checks verify one-profile restoration, cross-profile refusal, retained-version export/import round trips, comparison of added/removed values, current/plan-pinned retention, queued-history deletion refusal and copied-file preservation. Another native form fixture fills three of four draft slots and refuses an oversized recursive folder before review, preserving the old selection. Selection capacity is independently checked against the actual current draft and final import.

Native Firefox checks exercise comparison and restoration without creating a second profile. Card/filter checks verify two-line full-name tooltips, centered folder navigation and bounded filter-menu scrolling against each active Boost theme. Review rows toggle removal checks without disabling their existing import approval; the extra Save changes control is absent. Pencil rename still uses a native modal. Desktop/mobile review captures retain badges and destructive actions together without filename overlap.

## Responsive administration follow-up

Three targeted native scenarios pass on each exact core, with 60 steps per target: blank translated profile creation/editing, blocked migration reconfiguration and profile version comparison/restoration. A second correspondence row is added through the native button, edited, measured at desktop 1366 px and Firefox's effective 500 px mobile width, then removed without changing the original row. Checks cover page/editor overflow, visible control bounds, input alignment when labels wrap and serialized form values. History Back is checked at the left edge with its native secondary button and core icon. Inspected captures show native Moodle 4.5 and 5.3 controls, with the side drawer closed on mobile. The final drawer-closing adjustment also passes the blocked-migration scenario independently on 4.5: one scenario / 20 steps. The final 5.3 three-scenario run passes all 60 steps after an earlier loader timeout before fixture setup.

Both exact-core AMD builds pass ESLint/Rollup; 5.3 also generates declarations. Changed PHP and the complete 4.5 distributions pass coding standards without errors or warnings. The complete 5.3 source checks also pass with only the installed older checker's PHPUnit coverage-metadata sniff excluded, because it does not recognize PHPUnit 11 class attributes; those attributes remain in the 5.3 distribution and are covered by the recorded PHPUnit 11 run. Shared source comparison, syntax/XMLDB checks, local documentation links and Git whitespace checks pass. Both main sites receive the updated assets and cache purges without resetting their courses or files.
