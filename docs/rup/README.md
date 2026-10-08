# UML catalogue

The diagrams describe this package's supported behavior. Requirements, analysis, design and durable state models are kept separate. Every diagram has a local PlantUML source and SVG preview.

| Discipline | Content |
|---|---|
| [Requirements](requirements/README.md) | Context, actors and supported use cases |
| [Use-case details](requirements/use-cases/README.md) | Step-by-step conversations and alternatives |
| [Analysis](analysis/README.md) | Domain concepts and boundary/control/entity responsibilities |
| [Design](design/README.md) | Current classes, components, tables, presentation and sequences |
| [Behavior](behavior/README.md) | Matching, policy and migration processes; stored lifecycles |

![Context](../diagrams/requirements/system-context.svg)

Use-case identifiers retain their existing numbering. Only supported workflows appear in this catalogue. Search is read-only; publication and policies govern eligibility; reviewed migration preserves source content.

## Reading the diagrams

Use-case conversations arrange actor/system steps vertically. Sequence participants are ordered by call flow. Class and database views are split by responsibility; their arrows show direct dependencies or labelled record relationships. Layout-only hidden links keep related nodes aligned.

See the [diagram inventory](diagram-inventory.md) for source and preview pairs.

## Regenerate previews

Run locally from this repository:

```powershell
$diagrams = (Resolve-Path docs/diagrams).Path
docker run --rm -v "${diagrams}:/data" plantuml/plantuml --check-syntax "/data/**/*.puml"
docker run --rm -v "${diagrams}:/data" plantuml/plantuml --svg "/data/**/*.puml"
```

No external diagram rendering service is required.
