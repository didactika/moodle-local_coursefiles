<div align="center">

<img src="pix/icon.svg" width="96" alt="CourseFiles icon">

# CourseFiles for Moodle

*Find and reuse public course files across related courses*

[![Release](https://img.shields.io/github/v/release/didactika/moodle-local_coursefiles?style=flat-square)](https://github.com/didactika/moodle-local_coursefiles/releases)
[![Moodle](https://img.shields.io/badge/Moodle-5.3-f98012?style=flat-square&logo=moodle&logoColor=white)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL_v3-blue?style=flat-square)](LICENSE)
[![CI](https://github.com/didactika/moodle-local_coursefiles/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/didactika/moodle-local_coursefiles/actions/workflows/ci.yml)

[Overview](#overview) · [Installation](#installation) · [Usage](#usage) · [Configuration](#configuration) · [Troubleshooting](#troubleshooting)

</div>

CourseFiles (`local_coursefiles`) manages related-course profiles, a public-file catalog, original authorship and reviewed background migrations.

> [!IMPORTANT]
> Install both [CourseFiles local](https://github.com/didactika/moodle-local_coursefiles) and [CourseFiles repository](https://github.com/didactika/moodle-repository_coursefiles) from the same maintenance line. The standalone MultiPicker repository is a separate plugin.

## Overview

The local component owns profiles, their version history, a searchable metadata catalog, publication policies, original author provenance and migration jobs. repository_coursefiles exposes that library in native activity forms. Moodle’s File API stores all binaries; the plugin does not create another file store.

## Features

| Feature | Behavior |
|---|---|
| Related courses | Named regular-expression captures define course groups and up to nine configurable search filters. |
| Public material library | Folder, File, public Assignment attachments and other declared public providers; private user material remains excluded. |
| Multi-file selection | Grid, list and lazy recursive tree views share checks and unique-content counts. Clear filters retains selected material and copy options. Existing destination content is excluded before pagination. |
| Native review | Resulting-file tree, folder removal checks and pencil rename dialogs before importing into the native draft. |
| Native limits | Accepted types, remaining file count, directory support and byte limits are enforced again at import. |
| Profiles | Guided forms, bulk state actions, portable export/import, version comparison and restoration. |
| Migrations | Public source activity types mapped to Folder destinations; paginated review, automatic selection saving, explicit conflict choices and background execution without maintenance. |
| Retention | Configurable logs, migration history and inactive profile-version retention. |

## Requirements

| Requirement | This distribution |
|---|---|
| Moodle | 5.3 only |
| Maintenance line | main |
| Release / maturity | 5.3.0 / stable |
| PHP and database | Moodle’s requirements for the selected release; completed local checks use PHP 8.3 and PostgreSQL 17. |
| Companion | [repository_coursefiles](https://github.com/didactika/moodle-repository_coursefiles/tree/main) from the same maintenance line. |

Moodle 4.5 and 5.3 have separate distributions. Other 5.x versions are not declared supported. The local companion uses native Moodle 5.3 React/TypeScript for view controls alongside the native AMD editor/picker adapters.

## Installation

Install the local companion first, then the repository component. Download the reviewed release ZIP and use Site administration → Plugins → Install plugins, or copy this repository into **local/coursefiles** beneath the actual Moodle application directory. Moodle 5.3’s checked layout places that directory beneath public/.

Run the administrator upgrade screen or the native CLI upgrade:

```sh
php admin/cli/upgrade.php --non-interactive
```

Existing CourseBridge installations require a component rename: retain their records, policies and repository identities, migrate scheduled and queued task registrations, then remove the retired directories. Follow the detailed workspace rename procedure before upgrading an existing installation.

## Usage

1. Open Site administration → Plugins → Local plugins → CourseFiles.
2. Create and activate a profile defining related courses and supported public source areas.
3. Set the publication and author policy, then refresh the catalog through cron or course actions.
4. Enable the repository and use its native picker entry from an activity’s Add files action.
5. For site migrations, choose source/destination filters and activity correspondences, review the complete included set and queue the background job.

## Configuration

Guided profile forms expose course identification, grouping and dynamic named-capture filters. An advanced document editor remains available for supported rules. Migration correspondences belong to the migration wizard and select the source activity type separately from its matching rules. Site/course policy controls publication and authorship; retention settings bound inactive history.

Strict author protection refuses workflows where later native author changes cannot be prevented. Editable-with-provenance allows copying with the original declaration retained in the log. The original author and licence are preserved at copy time.

## Documentation and testing

Detailed explanations, the RUP UML catalogue, rendered diagrams and the exact validation matrix are maintained outside this plugin repository in the services workspace:

`projects/docs/in-progress/suite-coursebridge/moodle-local_coursefiles/`

That local collection covers both maintenance lines and is not published online. This README, [CHANGELOG](CHANGELOG.md) and [LICENSE](LICENSE) remain in each distribution. The package ships English language strings only; Moodle language packs can provide translations.

Run the packaged PHPUnit and native Behat tests in a disposable Moodle checkout with both companions installed and independent test databases. Build frontend assets with the exact supported core tooling. All custom behavior lives in TypeScript ESM feature modules, with a native React view selector and six AMD entry shims for existing Moodle calls. Typed adapters isolate core APIs that remain in AMD. Nested SCSS component sources generate the shipped styles.css; use `node tools/build-styles.mjs /path/to/moodle` to rebuild it. Local validation distinguishes completed checks from pending remote pipelines; it does not claim untested runtime combinations.

## Security and privacy

Session-bound scopes, capabilities, live publication checks and native draft ownership are verified server-side. Private submissions, feedback and personal files are not public providers. Interactive selection is bounded to 200 source occurrences; oversized requests are refused rather than truncated. Migration destinations are native Folders; the plugin copies public files rather than recreating whole activities.

## Support and contributions

Report reproducible issues through [GitHub Issues](https://github.com/didactika/moodle-local_coursefiles/issues), including the Moodle version, plugin release and steps without private data. Submit changes against the correct maintenance line and keep companion contracts aligned. Review [the changelog](CHANGELOG.md) and the documented test evidence before upgrading.

## License

Copyright © 2026 Didactika.org — Hector Arrechea <hectorlazaroarrechea@gmail.com>.

Licensed under the GNU General Public License v3 or later. See [LICENSE](LICENSE).

## Troubleshooting

| Problem | Check |
|---|---|
| No materials appear | Enable a matching profile, confirm source visibility and run catalog reconciliation. |
| Old component names remain after upgrading | Follow the component adoption procedure below before installing the standalone MultiPicker. |
| Import is unavailable | Check destination permissions, native file limits and unresolved review conflicts. |

## Existing MultiPicker installations

Back up the database and plugin directories. Place CourseFiles in `local/coursefiles` and `repository/coursefiles`, then inspect and apply the metadata adoption:

```bash
php local/coursefiles/cli/adopt_multipicker.php
php local/coursefiles/cli/adopt_multipicker.php --execute
# Move the retired local/multipicker and repository/multipicker directories outside Moodle.
php admin/cli/upgrade.php --non-interactive
```

The procedure preserves table rows and IDs, settings, permissions, repository instances and supported queued work. It does not copy or delete course file binaries. Finish adoption before installing the new independent `repository_multipicker`.
