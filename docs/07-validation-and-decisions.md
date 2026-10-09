# Validation record

Verified on 2026-10-09, package version **2026100902**. PHPUnit and Behat use independent databases/dataroots. The main localhost installations retain their existing courses and native files; their CourseBridge metadata was adopted by MultiPicker without resetting those sites.

| Exact core | Maintenance branch | PHP / database | Combined plugin PHPUnit | Native Firefox / Boost |
|---|---|---|---|---|
| Moodle 4.5.13, build 20260810, core 8cbae18a2898cfd8266ec91ac206e12004f0ff5f | feat/multipicker-45 | PHP 8.3.33 / PostgreSQL 17.10 | 102 tests / 460 assertions passed | Local: 13 scenarios / 177 steps; repository: 3 / 37, passed |
| Moodle 5.3, build 20261005, core 426222 | feat/multipicker-5 | PHP 8.3.33 / PostgreSQL 17.10 | 102 tests / 461 assertions passed | Local: 13 scenarios / 177 steps; repository: 3 / 37, passed |

Moodle 5.3's PHPUnit 11 reports one existing PHPDoc coverage-metadata deprecation. The successful runs have no plugin test errors or PHP notices. Under heavy concurrent local indexing, Boost loader readiness occasionally exceeds Behat's ten-second hook timeout; complete suites pass on rerun without changing core or weakening functional assertions. Other PHP/database combinations, releases, themes and browsers are outside this matrix.

## Native selection and source authority

Firefox enters through the actual native Add files action or Tiny Link browser and the repository sidebar. Tests cover existing Folder content editing, existing File settings, an existing URL activity editor, content-only teachers without activity-settings permission, batch Save/Cancel, main-file preservation, off-page removal, Back from separate review, functional layouts and Assignment handouts. Tiny's two links are checked before saving and after reopening the activity settings.

Automatic filters replace a Moodle Fragment rather than the embedded document. The filename-search test checks retained focus. A server regression rejects unrelated fragment contexts, preserves off-page items and refuses approval from a submitted fragment action. Native control tests also cover actual empty drafts, new activities, count/type/byte/directory limits, expired scopes, session keys, replay, changed drafts/sources/profiles and transactional rollback.

Public-area tests cover Assignment additional files/instructions, exact-core timed visibility, Forum introduction files, Page content and visible Book chapters. Student submission components and hidden chapters are rejected. Guided source checkboxes preserve specific advanced introduction rules while opting into Assignment handouts. An unadapted text editor creates no batch scope.

Catalog tests cover arbitrary named captures, optional values, fresh identity against stale indexes, contextual Moodle Multilang names, publication and revoked access. A sixty-file fixture produces distinct 50/10 pages and restores an off-page checkbox on return. Original author/licence declarations remain in immutable history.

## Layout and theme evidence

Browser and review screenshots were inspected at 1366 px and Firefox's effective 500 px minimum width. Embedded panels have no horizontal overflow at those sizes. Mobile repository navigation uses a scrollable horizontal strip; review uses a collapsible folder tree. Core layout controls are hidden only while the embedded chooser is active; the plugin's Cards/List controls change its actual result layout.

Scoped CSS uses theme variables and component icons/templates remain overridable. These checks do not certify every theme, viewport, keyboard workflow or screen reader.

## Installation, upgrade and builds

Both exact cores install the thirteen-table XMLDB schema. Development-site rename checks preserve twelve legacy table row counts, profile/log IDs, the chosen editable-with-provenance policy, explicit CAP_PROHIBIT overrides and repository type IDs. Legacy tables remain rollback records. The parent UUID column has no invalid empty CHAR default. The capture-value table is reconstructible.

Both own-core Grunt pipelines pass ESLint/Rollup for browser and editor_import AMD modules; Moodle 5.3 also generates declarations. The separate Moodle 5 artifact declares Moodle 5.3 as its actual minimum. React is unnecessary for this integration: core owns the picker/editor, and PHP/Mustache/Fragment APIs supply the chooser. Shared server contracts remain aligned.

Moodle coding standards, PHP syntax, XMLDB XSD, Markdown links and sibling UML previews pass. Localhost demo profiles explicitly enable the new providers and each demo source Assignment has two indexed public teaching files; other profiles keep their previous allowlists.

## Practical boundaries

Interactive batches resolve at most 200 source occurrences, including selected Folder subtrees. Empty directories are not transferred. Tiny Link supports multiple links; image/media subpickers retain their native single-file type contract. Other editors and unregistered controls keep ordinary native behavior. Only explicitly declared public providers are indexed; JSON cannot publish arbitrary private areas.

Strict author policy refuses unsupported later native metadata protection. Editable-with-provenance preserves the original declaration but permits later native author edits. Bulk administration uses a separate reviewed existing-folder plan and maintenance/exclusive-editing requirements. Backup restore does not remap plugin lineage.
