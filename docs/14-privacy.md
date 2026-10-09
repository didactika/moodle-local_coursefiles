# Privacy requests

MultiPicker implements Moodle's [Privacy API](https://moodledev.io/docs/4.5/apis/subsystems/privacy). Its technical actor records belong to the system context, even when an action concerns a course. Requests approved only for a course context do not export or erase these system records.

## Discovery and export

The provider discovers actors from eight tables: `revision`, `coursepolicy`, `publication`, `attribution`, `origin`, `plan`, `run` and `log`, all with the `local_multipicker_` prefix. Context user discovery excludes the removed-actor value `userid = 0`.

For an approved system-context export, each table's rows linked to the requested actor are written under **MultiPicker / table suffix**. The export includes those complete records, including configuration, timestamps, declared attribution and log details. It does not include another actor's rows or file binaries.

## Individual and bulk deletion

![Approved actor deletion](diagrams/behavior/processes/privacy-deletion.svg)

Individual deletion and approved multi-user deletion have the same scope. The provider marks each selected actor's migration runs as cancelled, removes their complete log rows, and replaces their technical `userid` with zero in the other seven tables. Empty user selections do nothing. Unapproved actors keep their records and runnable status.

The remaining record fields stay unchanged: profile revisions, publication flags, plan contents, original author/licence declarations and lineage links remain available. This operation removes technical actor linkage; it does not claim to remove every personal name that someone might have entered into free-text metadata.

System-context deletion for all users removes every log row, unlinks all technical actors and marks all runs cancelled, including runs already linked to actor zero. Requests for other context levels do nothing.

## Storage boundaries and verified scope

Native Moodle files remain owned by their core component. MultiPicker does not delete their contents, change their native uploader identity or edit their declared author in its privacy callbacks. Those native records have their own core privacy handling. The repository plugin stores no additional personal records.

The [privacy integration tests](../tests/privacy/provider_test.php) verify approved actor isolation across all eight tables, context boundaries, empty bulk requests, cancellation, unchanged lineage and preservation of native file contents and metadata. These checks run against both required cores; see the [validation record](07-validation-and-decisions.md). They establish these callbacks' behavior, not automatic identification of authors from free-text declarations.
