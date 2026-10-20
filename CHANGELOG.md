# Changelog

## [Unreleased]

- Show how many of the filters hidden under "More filters" are applied, as a count on the corner of the funnel icon, the same one the other plugins use.

## [1.0.0-m400] - 2026-10-11

First release of the Moodle 4.0 to 5.2 line, on `MOODLE_400_STABLE`. The Moodle 5.3 line is on `main` (1.0.0-m503).

### Added

- Profiles of related courses, defined by named regular-expression captures that can also become search filters, with guided editing, import and export, retained versions, comparison and restoration.
- A file picker with cards, list and lazy tree views, recursive multiple selection of the public material of other courses (Folder, File, Assignment handouts and other registered providers), destination limits, and material already used in the destination excluded before browsing.
- A review of the resulting file tree before copying, with removal checks and dialogs for destination names.
- Migrations between courses with source filters and activity correspondences. A window checks what a migration would do before it is created, so parameters that find nothing to migrate are changed without creating one. What does not match (a folder the source does not have, a destination without courses, a hidden folder) is counted as information and is never an error. What happens with the files that are already different in the destination is decided in the last step, where it is known how many there are, so a migration never waits for a decision once it exists and one with nothing to write is not created; every change, including new parameters, is saved in the same migration, which is only duplicated by the button that says so. The lists of choices are bounded (ten profiles at a time, searched by name, and the last ten migrations of a profile to take its folders from). The screens are the same application as in the 5.3 line, drawn by Preact, which gets everything from web services, with paginated files and folders and execution in the background.
- Original authorship declarations preserved at copy time, with configurable protection and provenance.
- The screens use the Bootstrap classes of the theme, with the design of the plugin as rules scoped to `.filebank-ui`, so a theme restyles buttons, cards, badges, tables and the rest as it restyles the site (see Theming in the README).
- A bounded log and configurable retention of logs, migrations and inactive profile versions.
- The interface is written in AMD modules and Mustache templates, and the migration screens in TypeScript that is built into one AMD module, with the same features as the 5.3 line.
