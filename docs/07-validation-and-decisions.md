# Validation record

Checks use isolated Moodle PHPUnit and Behat databases. Verified on 2026-10-09, plugin version `2026100901`.

| Exact core | PHP / database | Local PHPUnit | Repository PHPUnit |
|---|---|---|---|
| Moodle 4.5.13, build 20260810 | PHP 8.3.33 / PostgreSQL 17.10 | 73 tests / 352 assertions passed | 11 tests / 40 assertions passed |
| Moodle 5.3, build 20261005 | PHP 8.3.33 / PostgreSQL 17.10 | 73 tests / 352 assertions passed | 11 tests / 40 assertions passed |

The matching maintenance lines are `feat/coursebridge-admin-layout-45` and `feat/coursebridge-admin-layout-5`. Shared PHP contracts and native behavior are aligned. Moodle 4.5 builds AMD with the exact core Grunt ESLint/Rollup tasks. Moodle 5.3 runs its own AMD build, including its declaration generation task; the executable AMD bundle has the same SHA-256 on both cores. React is unnecessary for this native picker integration. Other core versions, PHP/database combinations and themes are outside this evidence.

## Service and native-control coverage

The suites cover configurable course identities and optional editions, content grouping, current visibility/publication, role permissions, malformed selectors, author policy changes, conflict decisions, draft ownership, session keys, expiry, bounded session state, replay, changed source/profile/draft/destination, count/type/size/directory limits and transactional rollback.

Native-control checks exercise real Folder/File forms, the separate Folder content editor, a teacher prohibited from changing activity Settings, and a new activity's native draft. Folder modes include enclosing-folder and content-only mapping, parent/child overlap, stale-approval invalidation and actual mapped draft copies. Resource supporting copies preserve the destination main file through native saving. The repository checks native batch transfer and the all-skipped-content response as well as individual access/copy contracts and original attribution after later editable metadata changes.

## Browser acceptance

Firefox/Boost scenarios exercise the actual native repository AJAX and embedded HTML chooser. The local package has five scenarios / 62 steps: two source folders plus a File, native Folder Save, Cancel, content-only teacher permissions, and File supporting materials/main-file workflow. The repository has three scenarios / 39 steps: unique content results with hidden-material exclusion, combined filename/edition/shortname/module/type filters and native saving, and retained selection after filtering. Test fixtures explicitly publish sources to a destination-only teacher and choose editable-with-provenance; the package's strict default remains unchanged.

The embedded browser and native entry are inspected at desktop and 390 px widths. Explicit labels, checkbox cards, disclosure controls and a list review keep the plugin presentation within its available width. These checks do not certify every native dialog, theme, browser or screen reader.

## Installation and upgrade

Both required cores install the XMLDB schema in isolated test sites and complete the CourseBridge `2026100901` upgrade on the localhost development sites. The parent UUID column keeps no empty CHAR default. Log upgrades preserve action history; capability changes preserve explicit grants/prohibitions. This interface update adds no business tables. Existing unrelated Moodle 4.5 plugins can produce CLI default-setting notices; they are outside these packages.

Moodle coding standards and the versioned UML sources/previews are checked with the current source. Diagrams describe implemented routes and contracts. Markdown links and generated previews are checked locally.

## Practical boundaries

The strict author policy refuses copying through native editors with unprotected later metadata changes. Editable-with-provenance preserves the original declaration in history and permits later native editing. Administrative bulk migration requires maintenance mode and an exclusive editing window; a CourseBridge lock does not coordinate unrelated native editors. Backup restore does not remap plugin lineage. Visual batches are bound to supported Folder/File filemanagers, not arbitrary upload, editor or backup/import controls. Each batch resolves at most 200 source occurrences and preserves existing files additively.

## Administration layout checks

Three additional PHPUnit cases exercise delegated tab permissions, unsaved configuration parsing without a revision write, original-revision summaries and whole-plan counts across filtered 50-file pages. The Moodle 4.5 combined plugin suite passes 84 tests / 392 assertions. On Moodle 5.3 the same suites and the focused output (7 tests / 38 assertions) and administration (3 tests / 19 assertions) files validate the updated grouped template contract. PHPUnit 11 reports PHPDoc coverage metadata deprecations in test declarations.

The native administration browser feature has four scenarios / 51 steps: site settings saving, unsaved profile preview/Cancel, edition configuration and grouped migration review/filter/back navigation, and delegated profile access without site configuration. Desktop and mobile captures use the exact Boost theme with native drawers closed for mobile inspection. Occasional before-step timeouts in the core theme loader were rerun separately; they are outside the plugin route code. The picker AMD bundle is unchanged by this server-rendered layout update.
