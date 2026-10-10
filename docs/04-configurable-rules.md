# Course grouping and search filters

Profiles describe which courses belong together and which public material areas are searchable. Folder correspondences, section matching and destination conflict choices belong to each migration.

![Profile configuration](diagrams/design/sequences/uc03-configure-profile.svg)

## Guided editor

The ordinary Moodle form edits the name, course identity field, named-capture expression, family template, matching dimensions, category scope and publication/source choices. There is no separate annual-edition setting. An edition, semester, cohort or another dimension is a configurable search-filter row.

The filter table has editable key, capture, label and optional columns, Add row and a native delete action. Keys are stable technical identifiers; labels are independent display text. Each capture must exist in the expression; duplicate keys, missing labels and invalid patterns are refused. A profile has at most nine filters. Removing all rows stores an explicit empty filter set.

For example, an expression can capture year and term, while the filter keys remain edition and semester:

```json
"filters": {
  "edition": {"capture": "year", "label": "Academic year", "optional": true},
  "semester": {"capture": "term", "label": "Semester", "optional": false}
}
```

Labels pass through Moodle format_text with cleaning and configured text filters. Native course and profile names use format_string. Technical keys, expressions, filenames and input attributes remain escaped. Suggestions contain at most 100 values from the authorized result page.

## Advanced rules and portable profiles

Advanced mode replaces the guided grouping, filter and public-source sections with the complete JSON editor. The ordinary form does not require JSON or YAML. New-profile import and selected/all export use portable JSON; importing validates the complete bundle before creating independent profiles.

Preview rules validates submitted fields without saving. Saving creates a new current configuration version under the same profile identity. Earlier versions remain available for comparison and restoration; see [profile history](25-profile-version-history.md). Guided saves remove the old separate edition and migration configuration from that profile; legacy documents remain readable, and existing migration plans retain their original reviewed rules.

Saving or enabling a profile queues catalogue reconciliation through Moodle cron. Each task examines at most 25 courses before queuing the next slice, and ignores a revision that has since been replaced. Name and publication edits reuse indexed membership immediately when the relationship rules and category scope are unchanged; live publication checks still apply. Changed grouping or capture rules become searchable after their matching courses are rebuilt. An empty picker distinguishes a matching course awaiting its current index from an ordinary empty search. Cron must process queued tasks for this background update to complete.

## Identity and publication

A parser reads idnumber, shortname or fullname using delimited PCRE with named captures. Required captures, explicit trim/case normalizers, a family template and optional matching dimensions resolve the identity. Multiple parsers are evaluated; conflicting identities fail unless first-match order is explicitly configured. Pattern/input size and PCRE work are bounded.

The [institutional example](../examples/institutional-profile.json) groups UUID||code||year identifiers by UUID. The [generic example](../examples/generic-profile.json) groups subject and term names without requiring annual editions. Missing optional values remain absent. Legacy profiles without a filters object retain their existing edition capture as a compatibility fallback.

Public-source checkboxes opt into declared teaching file areas. Private submissions, feedback and user files cannot become sources through arbitrary JSON. Historical modules require ordinary source access or an explicit eligible module publication. See [public sources](22-public-material-sources.md), [migration rules](06-migrations.md) and [administration](20-administration-layout.md).

New profiles open with empty text fields, no categories, no capture rows and no selected public sources. Guided submission constructs a minimal supported document without inheriting an institutional example. Every editable native field has a Moodle help icon. The save/preview/cancel group sits above the fields. Empty visible-group labels use the group key.

Column schemas and translated options are stored in escaped data attributes on their native textareas. AMD receives only the field ID; large correspondence schemas never become js_call_amd arguments.
