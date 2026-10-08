# Architecture

CourseBridge has two components. **local_coursebridge** owns data, rules, authorization and administration. **repository_coursebridge** owns native picker listing, filters, origin selection and draft copying. It delegates business decisions to the local plugin.

![Components](diagrams/design/components.svg)

![Deployment](diagrams/design/deployment.svg)

## Domain responsibilities

![Conceptual classes](diagrams/analysis/domain-concepts.svg)

![Boundary, control and entity responsibilities](diagrams/analysis/boundary-control-entity.svg)

An asset is a virtual content group. Authorization and author declarations belong to concrete occurrences. Profiles, grants, policies and reviewed migration records have separate identities.

## Current service classes

Services are grouped under `classes/local/catalog`, `profile`, `policy`, `migration` and `logging`, with matching PHP namespaces. Administration and course controllers are separate. See the [source structure](13-source-structure.md).

![Catalog design classes](diagrams/design/classes.svg)

`profiles` activates immutable JSON revisions; `profile_config` resolves generic families, optional editions and pairing dimensions. `indexer` rebuilds per-course metadata, while `catalog` streams candidates and `material_source` checks live visibility, native access or explicit grants.

![Migration design classes](diagrams/design/migration-classes.svg)

`migration_planner` resolves course pairs and placements, snapshots each decision and stores the preview digest. `placement` matches sections/modules; `copy_decision` distinguishes byte equality from attribution equality. `migration_executor` revalidates intent under destination locks; `folder_destination` stages native draft content and calls the folder update contract.

![Author policy classes](diagrams/design/authorship-classes.svg)

`author_policy` resolves site/course/source protection and captures the original declaration. Native editor paths allow later author changes, so strict copying is refused. The repository cannot turn metadata preservation into universal editing prevention.

## Content and access

Moodle File Storage owns binaries, drafts and native course usages. Identical bytes share native content storage; independent usages retain their native records. CourseBridge adds no binary pool or stored asset table.

The occurrence index is reconstructible. Refresh replaces one course's cached records in a transaction under a lock. Module/course events enqueue refresh work; reconciliation visits up to 100 courses per invocation. Active profile revisions exclude stale membership.

![Index refresh](diagrams/design/sequences/index-refresh.svg)

Every result and selection uses current family membership, course/module/section visibility, availability restrictions and source authority. Tokens and cached locators do not confer access. Ordinary access and explicit family grants follow distinct checks.

Catalog rows are streamed in content order. Filtering and authorization precede grouping; the cursor advances after a complete hash group. Profile and native scope lookups are cached for the request. Destination content hashes are indexed once per target folder during preview.

## Presentation boundary

Page controllers prepare plain data, component renderers select Mustache templates, and the active theme supplies the visual styling. Services do not return presentation HTML. Both plugins expose stable template names and region classes; see [presentation and theme overrides](11-presentation-and-theming.md).

## Lifecycle

The package uses Moodle DML/XMLDB, Task, Lock, capabilities, language strings and Privacy APIs. Native copied files survive plugin removal. Core course backup includes copied files; this package does not remap plugin lineage on restore. Technical actor exports and anonymization remain distinct from material author declarations.
