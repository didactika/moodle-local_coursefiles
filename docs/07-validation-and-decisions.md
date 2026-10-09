# Validation record

Checks use isolated Moodle PHPUnit and Behat databases. They do not modify a production campus.

| Core | PHP / database | Local plugin | Repository plugin |
|---|---|---|---|
| Moodle 4.5.13, build 20260810 | PHP 8.3.33 / PostgreSQL 17.10 | 59 tests / 293 assertions passed | 8 tests / 31 assertions passed |
| Moodle 5.3, build 20261005 | PHP 8.3.33 / PostgreSQL 17.10 | 59 tests / 293 assertions passed | 8 tests / 31 assertions passed |

Verified on 2026-10-08, plugin version 2026100802. Privacy coverage is expanded on `test/coursebridge-privacy`; the earlier structure and repository checks used `refactor/coursebridge-structure`. Moodle coding standards 3.7.0 report zero errors and warnings across both packages. The existing 55 PlantUML diagrams and the added privacy process render to SVG without syntax errors. Local Markdown links resolve.

The repository suite and native browser checks were verified on 2026-10-09 in `test/coursebridge-native-picker`. All three Behat scenarios and 52 steps pass per core: explicitly published multi-edition origins, hidden-material exclusion, native AJAX filters, draft selection and folder save retaining existing files, plus Tab order and Enter submission. Moodle's axe check passes for the repository search region. These checks exercise a teacher enrolled only in the destination course.

The local service checks were expanded on 2026-10-09 in `feat/coursebridge-draft-batches`. Per core, the complete 45-test suite passed with 244 assertions, followed by the added multiple-folder case (one test / three assertions). Eleven batch cases add 52 assertions to the previous local suite. They exercise mixed overlapping selections, several source folders, configured cross-path content skipping, per-file declarations through native saving, repeat application, control limits, foreign drafts, hidden modules, stale drafts/profile revisions, changed strict policy and a real core-hook failure during the second addition. That failure rolls back all batch file/attribution records and preserves existing content. These are internal PHP integration checks; multiple selection and browser filemanager refresh are not implemented. See [the service contract](15-draft-batches.md).

The complete local suite was verified again on 2026-10-09 in `feat/coursebridge-form-scope`: 59 tests / 293 assertions per core. Thirteen owning-form cases add 46 assertions. They compare real registered native control options with core normalization, simulate a no-submit form refresh preserving pending data, verify unchanged activity content before ordinary saving, and cover session keys, actor binding, expiry, replay, changed limits, upload ceilings, explicit close, frozen fields, new/foreign drafts, revoked module permissions and the 32-entry session bound. Moodle coding standards report zero errors and warnings. The [scope API and rendered design sequence](16-native-form-scope.md) describe this developer integration; no automatic module-form replacement, batch endpoint or multiple-selection browser screen is installed.

The integration tests exercise optional editions, ambiguous and invalid parsers, content/attribution conflicts, target authority, content grouping, edition filters, stale source visibility, strict-copy rejection, idempotent folder copies, cross-path duplicates, changed profiles and changed destinations.

Output checks render real component templates, exercise escaping and label/control associations, and verify readable states. The installed XMLDB schema validates against Moodle's XSD. PHP uses Moodle coding standards; PlantUML sources are rendered locally and Markdown links resolve.

Log checks exercise denied access, a dedicated reader role, exact filters, cursor pagination after new inserts, escaped output and approved actor deletion. Upgrade checks preserve legacy record IDs and contents, create indexes, resume after a rename, refuse conflicting tables, and retain explicit role grants and prohibitions.

Six dedicated privacy tests add 82 assertions per core: complete actor-scoped exports across all eight tables, context and empty-request boundaries, user discovery excluding unlinked actors, individual and bulk deletion, system-wide deletion, run cancellation and unchanged records for unapproved actors. The native folder file retains its contents, uploader and declared author. These tests verify technical actor unlinking; free-text author declarations are not resolved to Moodle user accounts. See [privacy behavior and boundaries](14-privacy.md).

Native author/save checks exercise direct-adapter strict refusal before draft creation, source and target course locks, preservation of existing nested destination files and metadata, policy changes after picker selection, draft source serialization, save/reopen/rename and unchanged original attribution after current metadata edits. A failed core file-size check rolls back capture. These use core APIs rather than a complete browser form session.

Fresh isolated installations load the schema through Moodle XMLDB. The real parser accepts the required `parentuuid` field without an empty default or normalization notice. The 2026100801-to-2026100802 upgrade preserves root and child attribution records. Renderer tests load the organized component template paths, and listing tests exercise the extracted picker helpers with authorized native occurrences.

## Scope of the evidence

Firefox rendered the actual component templates with each core's compiled Boost stylesheet at 1366 px and 390 px. Catalog, migration report, policy and picker search samples have no page-level horizontal overflow; search inputs accept typing. These are rendered fixture checks, not full native picker session acceptance or accessibility certification.

The added native picker scenarios use the real modal and forms, separate from those earlier render fixtures. Visual checks also opened that modal at 1366 px and inside a same-origin 390 px viewport on both cores: the search grid fits in two desktop columns or one mobile column without page-level horizontal overflow. Automated search-region checks and keyboard focus tests do not establish complete site, screen-reader, browser or theme certification.

The log template was also rendered with both Boost stylesheets at those widths. Its labelled filters and responsive record table have no page-level horizontal overflow.

These are alpha checks of the documented service and presentation contracts. The tested upgrades cover 2026100800 through 2026100802; they do not establish a stable-release upgrade history. The package has no plugin-specific backup lineage remapping. Native author editing remains permitted under editable policy and strict copying is blocked.

The [use cases](rup/requirements/use-cases/README.md) and [author policy](09-authorship-policy.md) describe the current behavior without treating absent integrations as shipped functionality.
