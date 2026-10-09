# Validation record

Verified on 2026-10-09, package version **2026100903**. PHPUnit and Behat use independent databases/dataroots. The main localhost installations retain their existing courses and native files; their CourseBridge metadata was adopted by MultiPicker without resetting those sites.

| Exact core | Maintenance branch | PHP / database | Combined plugin PHPUnit | Native Firefox / Boost |
|---|---|---|---|---|
| Moodle 4.5.13, build 20260810, core 8cbae18a2898cfd8266ec91ac206e12004f0ff5f | feat/multipicker-ui-45 | PHP 8.3.33 / PostgreSQL 17.10 | 110 tests / 498 assertions passed | Local: 17 scenarios / 231 steps; repository: 3 / 37, passed |
| Moodle 5.3, build 20261005, core 426222 | feat/multipicker-ui-5 | PHP 8.3.33 / PostgreSQL 17.10 | 110 tests / 499 assertions passed | Local: 17 scenarios / 231 steps covered, including targeted rerun; repository: 3 / 37, passed |

Moodle 5.3's PHPUnit 11 reports two PHPDoc coverage-metadata deprecations. The successful runs have no plugin test errors or PHP notices. Boost loader readiness occasionally exceeds Behat's default ten-second hook timeout in the WSL/Docker environment. Complete suites pass; the final Moodle 5 administration/repository run also passes with the existing Docker timeout factor set to 2. This changes test waiting time only, without changing core, plugin behavior or functional assertions. Other PHP/database combinations, releases, themes and browsers are outside this matrix.

After regenerating the saved Boost CSS, Moodle 4.5 passes the complete 17-scenario local suite. Moodle 5.3 passes 16 scenarios in that final suite; the remaining import scenario stops in the core Boost readiness hook before its fixture starts, even at the 20-second timeout. Its targeted rerun passes all 10 steps. Thus every current local scenario is exercised successfully, while the intermittent acceptance-environment loader issue remains recorded rather than presented as a consistently clean single run.

Guided profile editing and native JSON upload run through real Moodle forms. Service tests verify selected/all export, independent new-profile imports, atomic rejection, permission checks and actor-owned uploads. Migration checks cover capture scopes without annual editions, whole-plan inclusions, stale approval refusal, excluded operations, searchable 50-file pages and independent 50-issue pages. Native checks verify that checkbox exclusions survive searching and continuing to execution. The log is exercised through native Reports navigation; service checks cover date bounds and descending cursors.

## Native selection and source authority

Firefox enters through the actual native Add files action or Tiny Link browser and the repository sidebar. Tests cover existing Folder content editing, existing File settings, an existing URL activity editor, content-only teachers without activity-settings permission, batch Save/Cancel, main-file preservation, off-page removal, Back from separate review, functional layouts and Assignment handouts. Tiny's two links are checked before saving and after reopening the activity settings.

Automatic filters replace a Moodle Fragment rather than the embedded document. The filename-search test checks retained focus. A server regression rejects unrelated fragment contexts, preserves off-page items and refuses approval from a submitted fragment action. Native control tests also cover actual empty drafts, new activities, count/type/byte/directory limits, expired scopes, session keys, replay, changed drafts/sources/profiles and transactional rollback.

Public-area tests cover Assignment additional files/instructions, exact-core timed visibility, Forum introduction files, Page content and visible Book chapters. Student submission components and hidden chapters are rejected. Guided source checkboxes preserve specific advanced introduction rules while opting into Assignment handouts. An unadapted text editor creates no batch scope.

Catalog tests cover arbitrary named captures, optional values, fresh identity against stale indexes, contextual Moodle Multilang names, publication and revoked access. A sixty-file fixture produces distinct 50/10 pages and restores an off-page checkbox on return. Original author/licence declarations remain in immutable history.

## Layout and theme evidence

Browser and review screenshots were inspected at 1366 px and Firefox's effective 500 px minimum width. Embedded panels have no horizontal overflow at those sizes. Mobile repository navigation uses a scrollable horizontal strip; review uses a collapsible folder tree. Core layout controls are hidden only while the embedded chooser is active; the plugin's Cards/List controls change its actual result layout.

The current chooser has compact filters, whole-card checkbox selection, file/folder pills, small folder arrows, Back without repeated paths and selection management only in the review screen. Administration screenshots cover profile cards/import, four wizard steps and the action/date report. Native Boost acceptance CSS is rebuilt with the current plugin styles rather than relying on an older saved Behat theme.

Scoped CSS uses theme variables and component icons/templates remain overridable. These checks do not certify every theme, viewport, keyboard workflow or screen reader.

## Installation, upgrade and builds

Both exact cores install the thirteen-table XMLDB schema. Development-site rename checks preserve twelve legacy table row counts, profile/log IDs, the chosen editable-with-provenance policy, explicit CAP_PROHIBIT overrides and repository type IDs. Legacy tables remain rollback records. The parent UUID column has no invalid empty CHAR default. The capture-value table is reconstructible.

Both own-core Grunt pipelines pass ESLint/Rollup for browser, editor_import and administration AMD modules; Moodle 5.3 also generates declarations. The separate Moodle 5 artifact declares Moodle 5.3 as its actual minimum. React is unnecessary for this integration: core owns the picker/editor, and PHP/Mustache/Fragment APIs supply the chooser. Shared server contracts remain aligned.

Changed administration/profile/migration/selection PHP passes Moodle coding standards without errors or warnings. A complete plugin scan has no coding-standard errors; existing coverage-annotation, long-line and bootstrap-guard warnings remain outside that clean production subset. PHP syntax, XMLDB XSD, Markdown links, sibling UML previews and Git whitespace checks pass. Both sites upgrade to 2026100903 with existing operations included by default. Localhost demo profiles explicitly enable the new providers and each demo source Assignment has two indexed public teaching files; other profiles keep their previous allowlists.

## Practical boundaries

Interactive batches resolve at most 200 source occurrences, including selected Folder subtrees. Empty directories are not transferred. Tiny Link supports multiple links; image/media subpickers retain their native single-file type contract. Other editors and unregistered controls keep ordinary native behavior. Only explicitly declared public providers are indexed; JSON cannot publish arbitrary private areas.

Strict author policy refuses unsupported later native metadata protection. Editable-with-provenance preserves the original declaration but permits later native author edits. Bulk administration uses a separate reviewed existing-folder plan and maintenance/exclusive-editing requirements. Backup restore does not remap plugin lineage.
