# Validation record

Checks use isolated Moodle PHPUnit databases. They do not modify a production campus.

| Core | PHP / database | Local plugin | Repository plugin |
|---|---|---|---|
| Moodle 4.5.13, build 20260810 | PHP 8.3.33 / PostgreSQL 17.10 | 29 tests / 113 assertions passed | 8 tests / 30 assertions passed |
| Moodle 5.3, build 20261005 | PHP 8.3.33 / PostgreSQL 17.10 | 29 tests / 113 assertions passed | 8 tests / 30 assertions passed |

Verified on 2026-10-08, development branch `refactor/coursebridge-structure`, plugin version 2026100802. Moodle coding standards 3.7.0 report zero errors and warnings across both packages. All 55 PlantUML sources render to SVG without syntax errors. Local Markdown links resolve.

The integration tests exercise optional editions, ambiguous and invalid parsers, content/attribution conflicts, target authority, content grouping, edition filters, stale source visibility, strict-copy rejection, idempotent folder copies, cross-path duplicates, changed profiles and changed destinations.

Output checks render real component templates, exercise escaping and label/control associations, and verify readable states. The installed XMLDB schema validates against Moodle's XSD. PHP uses Moodle coding standards; PlantUML sources are rendered locally and Markdown links resolve.

Log checks exercise denied access, a dedicated reader role, exact filters, cursor pagination after new inserts, escaped output and approved actor deletion. Upgrade checks preserve legacy record IDs and contents, create indexes, resume after a rename, refuse conflicting tables, and retain explicit role grants and prohibitions.

Native author/save checks exercise direct-adapter strict refusal before draft creation, source and target course locks, preservation of existing nested destination files and metadata, policy changes after picker selection, draft source serialization, save/reopen/rename and unchanged original attribution after current metadata edits. A failed core file-size check rolls back capture. These use core APIs rather than a complete browser form session.

Fresh isolated installations load the schema through Moodle XMLDB. The real parser accepts the required `parentuuid` field without an empty default or normalization notice. The 2026100801-to-2026100802 upgrade preserves root and child attribution records. Renderer tests load the organized component template paths, and listing tests exercise the extracted picker helpers with authorized native occurrences.

## Scope of the evidence

Firefox rendered the actual component templates with each core's compiled Boost stylesheet at 1366 px and 390 px. Catalog, migration report, policy and picker search samples have no page-level horizontal overflow; search inputs accept typing. These are rendered fixture checks, not full native picker session acceptance or accessibility certification.

The log template was also rendered with both Boost stylesheets at those widths. Its labelled filters and responsive record table have no page-level horizontal overflow.

These are alpha checks of the documented service and presentation contracts. The tested upgrades cover 2026100800 through 2026100802; they do not establish a stable-release upgrade history. The package has no plugin-specific backup lineage remapping. Native author editing remains permitted under editable policy and strict copying is blocked.

The [use cases](rup/requirements/use-cases/README.md) and [author policy](09-authorship-policy.md) describe the current behavior without treating absent integrations as shipped functionality.
