# Validation record

Checks use isolated Moodle PHPUnit databases. They do not modify a production campus.

| Core | PHP / database | Local plugin | Repository plugin |
|---|---|---|---|
| Moodle 4.5.13, build 20260810 | PHP 8.3.33 / PostgreSQL 17.10 | 15 tests / 48 assertions passed | 4 tests / 13 assertions passed |
| Moodle 5.3, build 20261005 | PHP 8.3.33 / PostgreSQL 17.10 | 15 tests / 48 assertions passed | 4 tests / 13 assertions passed |

Verified on 2026-10-08. Moodle coding standards 3.7.0 report zero errors and warnings across both packages. All 51 PlantUML sources render to SVG without syntax errors. Local Markdown links resolve.

The integration tests exercise optional editions, ambiguous and invalid parsers, content/attribution conflicts, target authority, content grouping, edition filters, stale source visibility, strict-copy rejection, idempotent folder copies, cross-path duplicates, changed profiles and changed destinations.

Output checks render real component templates, exercise escaping and label/control associations, and verify readable states. The installed XMLDB schema validates against Moodle's XSD. PHP uses Moodle coding standards; PlantUML sources are rendered locally and Markdown links resolve.

## Scope of the evidence

Firefox rendered the actual component templates with each core's compiled Boost stylesheet at 1366 px and 390 px. Catalog, migration report, policy and picker search samples have no page-level horizontal overflow; search inputs accept typing. These are rendered fixture checks, not full native picker session acceptance or accessibility certification.

These are alpha checks of the documented service and presentation contracts. The package has no plugin-specific backup lineage remapping or established stable-release upgrade history. Native author editing remains permitted under editable policy and strict copying is blocked.

The [use cases](rup/requirements/use-cases/README.md) and [author policy](09-authorship-policy.md) describe the current behavior without treating absent integrations as shipped functionality.
