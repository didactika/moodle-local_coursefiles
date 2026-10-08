# Database and content identity

All application tables belong to **local_coursebridge**. The profile table is named `local_coursebridge`; every child table starts with `local_coursebridge_`. Moodle adds its configured database prefix at runtime. Use DML placeholders such as `{local_coursebridge_plan}`, never hard-code `mdl_`.

The installable schema is [db/install.xml](../db/install.xml). XMLDB foreign-key metadata describes plugin-owned relationships; no business record is owned by a disposable index row. The repository plugin has no installation tables.

![Installed table relationships](diagrams/design/data-model.svg)

## Installed tables

![Profile and catalog records](diagrams/design/catalog-data-model.svg)

![Reviewed intent and attempts](diagrams/design/migration-data-model.svg)

![Governance and provenance](diagrams/design/attribution-data-model.svg)

| Table | Purpose and identity |
|---|---|
| `local_coursebridge` | Relationship profile; unique UUID and active revision pointer |
| `local_coursebridge_revision` | Immutable JSON rules; unique profile/revision; SHA-256 document digest |
| `local_coursebridge_course` | Rebuildable membership; unique profile/course; active revision, family key/hash, nullable edition and pair dimensions |
| `local_coursebridge_file` | Rebuildable source occurrence; unique native pathnamehash; course/module, contenthash, size, filename and MIME |
| `local_coursebridge_publication` | Explicit module grant; unique profile/module; actor and current revocation flag |
| `local_coursebridge_coursepolicy` | Course override; unique course; policy/revision and actor |
| `local_coursebridge_attribution` | Original author/license declaration, content/source snapshot and parent lineage UUID |
| `local_coursebridge_origin` | Verified destination locator to an attribution record; unique pathnamehash |
| `local_coursebridge_plan` | Immutable preview JSON, digest, profile/revision, actor and state |
| `local_coursebridge_operation` | Unique plan/operation key; native source locator, destination module, snapshots, decision/state and structured result |
| `local_coursebridge_run` | Accountable bounded attempt, cancellation flag and outcome |
| `local_coursebridge_log` | Scoped actions and outcomes; actor identity removable through Privacy API |

No `local_cb_*` aliases remain. These names fit the XMLDB limits for the required Moodle versions. Binary content, draft areas and native course usages remain in Moodle's own file subsystem.

## Why there is no asset table

A material asset is the virtual key `contenthash + filesize`. Moodle already computes and stores byte identity. A second asset table would duplicate metadata, add joins and require garbage collection without providing publication authority. Group supported occurrence rows in the current query scope; retain each occurrence's own author, filename and source context.

## Distinct deduplication rules

1. Storage deduplication is native Moodle content-addressed storage.
2. Search groups identical permitted filtered content once, retaining the authorized origins.
3. Migration compares destination path, bytes and attribution. Same bytes with a different author/license are a conflict, not permission to overwrite metadata. Cross-path duplicates are reported/skipped according to the implemented policy.

![Attribution records](diagrams/design/attribution-data-model.svg)

Author is declared text, not the importer's account ID. Log and lineage records are separate from rebuildable course/file indexes. Course deletion or reindexing must not erase reviewed plan evidence. Source/target snapshots carry enough native information to detect stale content and placement.

## Indexes and query execution

The action log has composite action/ID and reference/ID indexes. Its reader uses a bounded descending-ID cursor and exact filters without a total-count query. The [log upgrade](12-log-and-upgrade.md) preserves records from the previous table name.

Unique pathnamehash prevents multiple entries for a native occurrence. Membership indexes scope a query to an active revision/family and native course. File indexes cover course/contenthash and module. Plans/operations have deterministic unique keys. Candidate records are streamed in content order; live source/module access is checked before aggregation and origin display. Counts apply only to authorized results.

![Authorized search sequence](diagrams/design/sequences/uc01-search-materials.svg)

## Reconstruction and privacy

Course refresh replaces only that course's cache inside a transaction; event delivery gaps are repaired by periodic reconciliation. Current source eligibility wins over a stale row. Durable attribution/plans do not reference the cache by foreign key. Privacy API exports actor-linked governance records and removes actor links; it does not delete other users' native course files.
