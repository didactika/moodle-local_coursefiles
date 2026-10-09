# Design views

The [activity navigation entry](../../19-activity-navigation.md) opens the supported native Settings workflow:

![Activity entry](../../diagrams/design/sequences/activity-entry.svg)

![Source folders](../../diagrams/design/source-structure.svg)

![Action log query](../../diagrams/design/sequences/log-query.svg)

The internal [draft batch service](../../15-draft-batches.md) has a separate sequence:

![Reviewed draft batch](../../diagrams/design/sequences/draft-batch.svg)

Explicit owning forms bind their native controls through the [session scope API](../../16-native-form-scope.md):

![Native owning-form scope](../../diagrams/design/sequences/form-scope.svg)

The native Folder Settings consumer has its own [screen contract](../../17-folder-selection.md):

![Folder mixed selection](../../diagrams/design/sequences/folder-selection.svg)

The native File consumer retains its [main-file choice](../../18-resource-selection.md):

![File main-file preservation](../../diagrams/design/sequences/resource-selection.svg)

![Components](../../diagrams/design/components.svg)

![Catalog classes](../../diagrams/design/classes.svg)

![Migration classes](../../diagrams/design/migration-classes.svg)

![Author policy classes](../../diagrams/design/authorship-classes.svg)

![Theme presentation boundary](../../diagrams/design/presentation.svg)

Installed table details are in the [database document](../../05-data-model.md). Each [use case](../requirements/use-cases/README.md) embeds its own design sequence. Shared integrations are described in [architecture](../../03-architecture.md).
