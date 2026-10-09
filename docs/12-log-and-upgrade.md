# Action log and existing installations

Open MultiPicker from Site administration and choose the Log tab. A delegated log reader enters through their authorized administration entry. The page requires `local/multipicker:viewlog` at system context. Managers receive this capability by default; a separate reader role can be granted access without profile or migration editing permissions.

The page shows the date, readable action, actor and reference. Actions cover profile activation, site/course author policy changes, publication changes, migration previews, queueing, blocked plans and processed batches. Profile and plan UUIDs are references; site policy changes have their own action reference. Actions without a shared reference leave that column empty. System actions and removed actors have a common fallback label.

## Reading records

Select an action and optionally paste an exact reference. Filters reset pagination. Each page reads at most 51 rows and displays 50, newest ID first. The older-records link retains filters and uses the last displayed ID as its cursor. Actions inserted between pages do not repeat or displace records on older pages. There is no total-count query.

![Authorized log query](diagrams/design/sequences/log-query.svg)

The page displays escaped record values and does not print the stored JSON details. The `local_multipicker/local/log/index` template is available for theme overrides. The service independently checks authorization before reading records. Privacy requests export approved actor records and delete that actor's log rows, including their details.

## Upgrade from version 2026100800

Version **2026100801** renames the existing action table to `local_multipicker_log` through XMLDB. It retains IDs, timestamps, actors, references and details. Fresh installations use the new table directly. Composite action/ID and reference/ID indexes support filtered cursor queries.

![Log schema upgrade](diagrams/behavior/processes/log-upgrade.svg)

A retry can continue when the table has already been renamed but the upgrade savepoint has not been written. Missing tables or simultaneous old and new tables stop the upgrade instead of discarding records. The savepoint is recorded after both indexes exist.

Moodle migrates explicit role permissions from the old read capability to `local/multipicker:viewlog`, including prohibitions, then removes the old capability. Historical names remain only in the upgrade compatibility code and its tests.

## Version 2026100802

This update removes the invalid empty-string default from the required `parentuuid` character field. The XMLDB field has no declared default; existing root and child provenance records remain unchanged. Fresh schema loading no longer needs XMLDB to normalize that declaration.

The code uses the organized [source folders and template identifiers](13-source-structure.md). Existing root page URLs continue to forward requests to the new controllers.

Deploy both plugin versions together. The repository plugin requires local_multipicker **2026100802**. Use Moodle's normal plugin upgrade; do not rename or recreate tables manually.

See [validation evidence](07-validation-and-decisions.md) and the [installed schema](05-data-model.md).

