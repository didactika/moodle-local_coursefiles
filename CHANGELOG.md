# Changelog

## [5.3.1] - 2026-10-13

- Number the version of the plugin with the lowest Moodle version of its line as a suffix, as a numeric string, so each maintenance line has its own and a newer line always has the higher one.
- Publish the releases automatically from `main` and the `MOODLE_*_STABLE` branches, keeping the latest release on `main`.
- Test the two ends of the supported range and the LTS releases between them, and cancel a run that a newer push supersedes.

## [5.3.0] - 2026-10-11

- Restore the native picker header, size and mobile layout when switching away from FileBank.
- Initial public FileBank distribution for Moodle 5.3.
- Related-course profiles with configurable capture filters, version history, comparison and restore.
- Native public-file selection in cards, list and lazy tree views with recursive folders and destination limits.
- Reviewed background migrations, conflict choices, original authorship records and configurable history retention.
- Native TypeScript/ESM frontend with the React browser integration.
- Own native picker cleanup in the parent document so repository changes also restore presentation in Chrome.
- Align filename/filter controls and responsive correspondence inputs with native theme components.
- Validate generated CSS without automatic corrections and complete Mustache validation contexts.
- Keep profile examples in the central documentation and generate portable test data inside Moodle's test environment.
- Bound native import paths before storage writes, retaining stable parent folders and source metadata when folder labels exceed the native path budget.
- Match CI stylesheet compilation to the installed Moodle Node runtime and support legacy Tiny toolbar controls in native acceptance tests.
- Separate validation, native listing and copy preparation into focused services without removing regression coverage.
- Enforce clean PHP Mess Detector reports and zero coding/documentation warnings in both maintenance lines.
- Use the official checksum-verified Moodle Plugin CI 4.5.11 artifact and password-authenticated PostgreSQL initialization.
- Refresh browser compatibility metadata with Node 22 and restore the core's own frontend build runtime.
- Install the initial public schema without obsolete upgrade callbacks.
- Preserve the CI archive suffix behind the command link and fail warning-only Mustache validation instead of accepting skipped child checks.
