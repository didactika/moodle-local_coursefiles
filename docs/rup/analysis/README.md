# Analysis classes

These diagrams describe conceptual responsibilities rather than PHP implementation classes.

![Domain concepts](../../diagrams/analysis/domain-concepts.svg)

A family relates courses, an edition is optional, and an occurrence retains its own author declaration. Content identity groups results without owning another binary.

![Boundary/control/entity](../../diagrams/analysis/boundary-control-entity.svg)

Teacher and administrator boundaries invoke separate search, profile, publication, policy and migration controls. Controls operate on authorized occurrences and durable review records. [Design classes](../design/README.md) name the executable collaborators.
