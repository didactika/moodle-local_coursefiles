# UC05 — Preview a configured migration

| Attribute | Behavior |
|---|---|
| Primary actor | Administrator |
| Goal | Review exact file decisions without changing native materials. |
| Preconditions | The actor has planmigration authority and can manage the destination courses. |
| Result | A reviewable or blocked immutable preview exists; native files are unchanged. |

## Step-by-step conversation

![UC05 conversation](../../../diagrams/requirements/use-case-details/uc05-preview-migration.svg)

[PlantUML source](../../../diagrams/requirements/use-case-details/uc05-preview-migration.puml).

| Step | Responsibility | Action |
|---|---|---|
| 1 | Administrator | Chooses a profile, source/destination capture filters and editable folder correspondences; confirms ordinal layout when applicable. |
| 2 | migration_planner / placement | Resolves unique family/dimension course pairs, sections and existing or explicitly requested destination folders. |
| 3 | copy_decision / author_policy | Compares source and target content, paths, declarations and copy policy. |
| 4 | migration_planner | Stores immutable intent, snapshots, decisions and digest. |
| 5 | Administrator | Reviews paginated files and placement issues, searches and saves inclusions or exclusions across pages. |

## Alternative outcomes

- Missing sources are informational; missing targets create blockers unless creation is explicitly requested. Ambiguous pairs/placements remain blockers.
- New migration settings skip identical bytes while retaining the destination declaration. Legacy reviewed attribution decisions remain unchanged.
- Unsupported strict copying blocks the initial plan.
- Selection changes update the approval digest before the first run; unresolved placement issues remain blockers.

## Design sequence

![UC05 sequence](../../../diagrams/design/sequences/uc05-preview-migration.svg)

[Sequence source](../../../diagrams/design/sequences/uc05-preview-migration.puml). Current collaborators: migration_planner, placement, material_source, copy_decision, author_policy.

[All supported use cases](README.md) · [Stored behavior](../../behavior/README.md).

Selection changes save automatically across pagination. Select all includes the full plan; individual/course/folder exclusions survive searches. Blocking placement decisions can be resolved within review by choosing an existing destination, proposing creation in an existing section, or excluding the source. The earlier plan remains in history. Continue is always present and disabled until the saved complete selection can execute. Execution requires explicit confirmation and never requires site maintenance.
