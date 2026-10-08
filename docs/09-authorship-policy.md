# Author metadata and site/course policy

The selected occurrence supplies its declared `author` and `license`. The importing teacher and technical uploader are action actors. CourseBridge does not infer authorship from their accounts or rewrite authors embedded in document contents.

![Policy and provenance classes](diagrams/design/authorship-classes.svg)

## Settings

| Setting | Values |
|---|---|
| `author_policy` | `preserve_locked` (default), `editable_with_provenance` |
| `author_course_override` | `disabled` (default), `tighten_only` |
| `author_missing_policy` | `preserve_unknown` (default), `block_reuse` |

The course Material policy page displays the effective policy and, when permitted, offers inheritance or a stricter lock. A course cannot weaken the site. Protected source provenance, source-course policy and destination-course policy combine: any lock requires protection.

![Policy resolution](diagrams/behavior/processes/authorship-policy-resolution.svg)

![Policy configuration](diagrams/design/sequences/uc09-configure-material-policy.svg)

## Native copying behavior

| Workflow | Author/licence behavior |
|---|---|
| Picker selection into a native draft | Source values are preserved; caller-submitted author values are ignored |
| Reviewed folder copy | Source values are preserved through native folder save and checked afterward |
| Any strict source/destination protection | Copy refused because subsequent native editor protection is unavailable |
| Editable source and destination | Initial declaration captured; later native editor changes permitted |
| Missing author with `block_reuse` | Copy refused; importer identity is never substituted |

These two plugins do not prevent every later native author change. The strict default refuses unsupported copying rather than claiming a lock. Editable-with-provenance must be chosen explicitly by an administrator.

## Native save boundary

The folder destination adapter checks source and target policy itself before preparing a draft. Calling it directly cannot bypass an effective lock. An editable site can still be tightened by either course. The picker checks policy again at copy time, so a reference selected before a policy change does not authorize a later copy.

On both tested cores, `stored_file::set_author()` writes through `stored_file::update()`; the `after_file_updated` callback runs after the database update. The native draft editor also uses this route. A post-write callback does not provide a before-write author lock. The creation hook documented by the [File API](https://moodledev.io/docs/4.5/apis/subsystems/files) does not establish protection for those later metadata updates.

This behavior was checked against the exact core implementations: [Moodle 4.5 stored_file](https://github.com/moodle/moodle/blob/8cbae18a2898cfd8266ec91ac206e12004f0ff5f/lib/filestorage/stored_file.php) and [Moodle 5.3 stored_file](https://github.com/moodle/moodle/blob/42622298fe06f9626d988d60b2bf589bd8f850e8/public/lib/filestorage/stored_file.php).

## Original provenance

Attribution records store the source declaration, native locator/content hash and parent UUID. Verified migration destinations are bound to those records. Picker drafts carry the attribution UUID in their native source metadata.

The picker uses Moodle's serialized draft-source envelope. Native save stores its inner JSON marker; reopening and renaming the file retains that marker in the tested path. Later editable author/licence changes do not rewrite the captured original declaration. A source marker is provenance metadata, not permission to reuse a file. Failed picker copies roll back their attribution record together with database file records.

![Stored provenance lifecycle](diagrams/behavior/states/attribution-lifecycle.svg)

Identical bytes can have different names and authors across origins. Search retains those declarations on separate origin rows; migration blocks identical-byte targets with conflicting author/licence metadata.

Privacy operations export and anonymize action actors independently of declared material metadata. This package exposes no correction/propagation action and does not remap plugin lineage on backup restore.
