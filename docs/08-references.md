# Official technical references

These references describe the core contracts used by the package. CourseBridge grouping, publication rules and reviewed intent are plugin behavior.

- [Repository plugins, Moodle 4.5](https://moodledev.io/docs/4.5/apis/plugintypes/repository): native picker integration and repository methods.
- [Repository plugins, Moodle 5.3](https://moodledev.io/docs/5.3/apis/plugintypes/repository): native listing/search/draft integration.
- [File API](https://moodledev.io/docs/4.5/apis/subsystems/files): component-owned native file usages.
- [File internals](https://moodledev.io/docs/5.3/apis/subsystems/files/internals): native content-addressed storage.
- [File Browser API](https://moodledev.io/docs/4.5/apis/subsystems/files/browsing): permission-aware native browsing.
- [Adhoc tasks](https://moodledev.io/docs/4.5/apis/subsystems/task/adhoc): queued accountable work.
- [Output API](https://moodledev.io/docs/5.3/apis/subsystems/output): renderers and plain template contexts.
- [Templates](https://moodledev.io/docs/4.5/guides/templates): component templates and escaping.
- [Theme plugins](https://moodledev.io/docs/4.5/apis/plugintypes/theme): theme template/renderer overrides.

The exact core checkouts are recorded in [validation](07-validation-and-decisions.md). Native draft saving and later filemanager edits can change author metadata; preserving initial metadata is distinct from preventing later edits.
