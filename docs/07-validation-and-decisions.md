# Validation record

Verified on **2026-10-10**, package version **2026100905**. PHPUnit and Behat use independent databases/dataroots. The main localhost installations retain their existing demo courses, files and owner-selected editable-with-provenance policy. No main-site reset is part of these checks.

| Exact core | Maintenance branch | PHP / database | Combined plugin PHPUnit | Native Firefox / Boost |
|---|---|---|---|---|
| Moodle 4.5.13, build 20260810, core 8cbae18a2898cfd8266ec91ac206e12004f0ff5f | feat/multipicker-workflows-45 | PHP 8.3.33 / PostgreSQL 17.10 | 123 tests / 549 assertions passed | Complete combined suite: 24 scenarios / 317 steps passed; final navigation correction: 1 / 9 passed |
| Moodle 5.3, build 20261005, core 426222 | feat/multipicker-workflows-5 | PHP 8.3.33 / PostgreSQL 17.10 | 123 tests / 550 assertions passed | Combined suite: 23 scenarios passed, one stopped before its fixture; complete administration rerun: 8 / 107 passed |

The combined native suite contains 21 local scenarios and three repository scenarios. Every scenario has successful coverage on both required targets. The final 5.3 combined run stops one administration background in the core Boost readiness hook before fixture creation: 308 steps pass and nine are skipped. Its complete eight-scenario administration rerun passes all 107 steps. An earlier full 19-scenario local run also passes on each version. This records the environment failure rather than presenting the final 5.3 combined run as consistently clean. The Docker timeout factor is three for final runs; it changes test waiting time only.

Moodle 5.3's PHPUnit 11 reports three PHPDoc metadata deprecations, with no failed assertions. Other PHP/database combinations, releases, themes and browsers are outside this matrix.

## Native picker evidence

Firefox enters through the actual Add files action or Tiny Link browser and the native repository sidebar. Tests cover existing Folder content editing, File settings, URL activity editors, new activities, native Save/Cancel, main-file preservation and content-only teachers without activity-settings permission. Public Assignment handouts and Tiny multi-link save/reopen are covered; private submissions remain excluded.

The configured repository instance name is checked in both the sidebar and embedded heading. Select all expands eligible recursive files, and Clear selection clears actual file checks. A 61-file service fixture verifies selection across multiple result pages. Parent/child selection, partial folder checks, overlapping roots and separate resulting-file review are exercised.

The review rename scenario clicks the native pencil, uses the actual Save/Cancel modal, returns to review and imports the changed destination filename. Source author/licence remains unchanged. Filename validation, approved name/path digests and individual/bulk removal have service coverage.

A delayed real Fragment response checks continued typing while results are loading. The same filter DOM node stays enabled and focused, the newest text wins, and the native modal height remains unchanged. Only results refresh; header/footer stay fixed. Unrelated fragment contexts, expired scopes, session keys, replay, stale sources/profiles/drafts, native type/count/byte/directory limits and transactional rollback remain covered.

## Administration and migration evidence

Native tests exercise settings/navigation, guided profile editing without JSON, unsaved rule preview, portable profile upload, paginated migration review, exclusions preserved across searching/navigation, execution discovery and the native Reports log entry. Service tests verify selected/all export, atomic new-profile imports, soft deletion with retained revisions, bulk enable/disable and refusal to queue with a disabled profile.

Migration regressions cover reviewed native destination creation, explicit overwrite, interrupted/idempotent execution, stale sources before creation, missing-source reporting, identical bytes with a different author declaration, and retention preserving active runs. Whole-course/folder inclusion spans more than one 50-operation page. Distinct source folders mapped to one destination remain separate review groups. Search/decision pages and issue pages are independently bounded to 50; queueing uses all included operations rather than only the displayed page.

## Layout and builds

Browser/review and administration captures were inspected at 1366 px and Firefox's effective 500 px minimum width. Embedded panels have no horizontal overflow at those sizes. Review shows readable filenames and a collapsible folder tree. Administration uses native version-specific navigation, profile/history/log tables, guided editable rows, right-aligned filter disclosures and a four-step migration flow. The saved Behat Boost CSS is regenerated after style changes.

The Moodle 4.5 partial uses core/moremenu; the 5.3 distribution delegates to core/secondarymoremenu and its own core React navigation. Five plugin AMD modules (browser, editor_import, administration, row_editor and review) pass each exact core's Grunt ESLint/Rollup build. Moodle 5.3 also generates module declarations. A plugin-owned TSX replacement is unnecessary for this native integration. The separate 5.3 artifact declares 5.3 as its actual minimum; no 5.3-only navigation/assets are shipped on the 4.5 line.

Both packages use the same supplied plugin icon; other controls use core icons. Old custom navigation/layout SVGs are removed. Templates, labels and scoped CSS remain theme-overridable. These checks do not certify every theme, viewport, keyboard workflow or screen reader.

After synchronizing the exact cropped source icon in both components, the configured-name/live-filter feature passes again on both targets: two scenarios and 18 steps each. The final picker capture confirms the repository heading/sidebar name and supplied icon together.

The final native select adapter is rebuilt against each core. Migration correspondence review and the configured-name/live-filter scenarios pass together on both targets: three scenarios and 41 steps each. The 5.3 capture has native form-select controls without deprecated Bootstrap 4 outlines.

## Installation and source checks

Both exact cores install the fourteen-table XMLDB schema and both main sites upgrade to 2026100905 without resetting existing material. Earlier component-rename checks preserve legacy row counts, profile/log IDs, policy choices, repository type IDs and explicit capability prohibitions. The parent UUID column has no invalid empty CHAR default. New operation group fields, target bindings, soft-deletion metadata and retention indexes have installation/upgrade coverage.

Changed production administration/form/migration/profile/selection/output/maintenance PHP and repository integration pass Moodle coding standards with no errors or warnings. Existing test annotation/long-line and bootstrap warnings are outside that production subset. PHP syntax, XMLDB XSD, literal template language identifiers, Markdown links, sibling UML previews and Git whitespace checks pass. Cache purges expose current strings and generated assets on both localhost sites.

## Practical boundaries

Interactive batches resolve at most 200 source occurrences, including selected Folder descendants; Select all rejects oversized batches instead of truncating them. Empty directories are not transferred. Tiny Link supports multiple links, while image/media subpickers retain native single-file contracts. Other editors and unregistered controls keep ordinary native behavior. Only declared public providers are searchable; JSON cannot publish arbitrary private areas.

Strict author policy refuses unsupported later native metadata protection. Editable-with-provenance preserves the original declaration while permitting later native author edits. Administrative migrations require maintenance mode and an exclusive editing window, with explicit reviewed creation/overwrite choices. History retention never removes native files or original attribution. Backup restore does not remap plugin lineage.
