# Changelog

## [Unreleased]

- Rename the related-course suite to FileBank and preserve existing MultiPicker metadata through an explicit adoption CLI.
- Support legacy core callbacks and editors on the shared Moodle 4.0–5.2 line; keep Moodle 5.3 native TypeScript/ESM integration separate.
- Shorten policy, publication and author table names to respect Moodle 4.0 XMLDB limits without losing records.
- Import retained versions from legacy profile bundles and validate every declared Moodle release in the CI matrix.


## [4.5.0] - 2026-10-10

- Rename the components and distributions to FileBank, preserve existing metadata through explicit adoption, and use the Didactika README format.
- Correct CI matrix parsing so repeated supported-version bounds remain separate values.

- Keep profile-defined search filters after all common controls and copy options so changing collections preserves their positions.
- Message Broker-style frontend feature structure: controllers, scoped actions, listeners, selectors and authenticated backend repository access.
- Nested SCSS component sources with generated flat CSS, shared responsive declarations and a CI freshness check.


- Explicitly hide course tree headings in Cards/List, synchronize view state across pending result loads, and clear all search restrictions without losing selected content.

- English-only source language strings; detailed documentation and UML moved to the central services workspace.

- Stable Moodle 4.5 distribution with Didactika copyright, author metadata and GPLv3 LICENSE.
- Correct Back navigation after changing layouts and compact lazy trees without broken grid grouping.
- Canonical selection counts overlapping content once across editions; existing draft content is excluded before browsing.
- Migration correspondence rows select a supported public source activity type and copy its authorized files into Folder destinations.
- One visible Local plugins entry, table-head profile selection and responsive correspondence controls.

- Native multi-file selection, configurable relationship profiles and reviewed background migrations on separate Moodle 4.5 and 5.3 distributions.
- One profile with retained configuration history, two-version checkbox comparison and activation of an existing version when restoring. Version-preserving portable bundles and bounded retention.
- Grid/list/course-tree views sharing material nodes, recursive folder selection, native file-count limits, stable filtering and scroll, two-line card names and core help popovers.
- Lazy, initially collapsed folder/subfolder/file tree with branch pagination and selection updates in place.
- Destination draft byte identity excludes already-used files before catalog pagination and recursive expansion; folder counts use one grouped query.
- Resulting-file review with native pencil rename dialogs, folder removal checks that preserve import approval and conditional removal beside Import materials.
- Direct Settings entry, native navigation, grouped coloured row actions and selection-aware profile bulk actions that update in place with core toast notifications.
- Responsive capture/correspondence rows, bounded administration pagination and automatically saved migration selection/conflict resolution. Execution requires no maintenance mode or extra acknowledgment checkbox.
- Explicit missing-destination creation, reviewed overwrite, identical-file skips, automatically cancelled empty migrations and inactive-history deletion without removing course files.
- Catalog refresh after profile activation and reuse of metadata when matching rules are unchanged.
- Upgrade 2026101001 removes retired CourseBridge scheduled tasks and preserves supported queued work under the new component name.
- Shared Bootstrap utilities in templates; organized component CSS limited to theme variables, responsive bounds, selection states and embedded picker behavior.
