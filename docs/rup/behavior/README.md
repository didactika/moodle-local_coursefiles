# Processes and stored states

![Preserved log schema upgrade](../../diagrams/behavior/processes/log-upgrade.svg)

Process diagrams describe decisions. State diagrams describe persisted records; they do not add conceptual review states that the schema does not store.

![Approved actor deletion](../../diagrams/behavior/processes/privacy-deletion.svg)

Individual and approved multi-user deletion share the same actor scope. See the [privacy explanation](../../14-privacy.md) for system-wide deletion and native storage boundaries.

![Course and folder placement](../../diagrams/behavior/processes/course-and-placement-matching.svg)

![Author policy resolution](../../diagrams/behavior/processes/authorship-policy-resolution.svg)

![Reviewed migration](../../diagrams/behavior/processes/migration-process.svg)

## Stored lifecycles

![Plan states](../../diagrams/behavior/states/migration-plan.svg)

A preview stores `reviewable` or `blocked`. Reviewing/executing does not mutate its immutable document. A changed revision blocks the attempt; changed source/destination snapshots block affected operations.

![Run states](../../diagrams/behavior/states/migration-run.svg)

![Publication flag](../../diagrams/behavior/states/publication-lifecycle.svg)

![Provenance lifecycle](../../diagrams/behavior/states/attribution-lifecycle.svg)

Cancellation stops at operation boundaries. Completed copies remain; a new accountable run on the unchanged plan addresses pending operations. Blocked operations require a new reviewed preview.
