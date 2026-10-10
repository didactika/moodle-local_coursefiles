# MultiPicker material library (local_multipicker)

Reusable public teaching files across related Moodle courses, with configurable grouping, native multi-file selection and reviewed background migrations.

[![Moodle Plugin CI](https://github.com/didactika/moodle-local_multipicker/actions/workflows/ci.yml/badge.svg?branch=MOODLE_405_STABLE)](https://github.com/didactika/moodle-local_multipicker/actions/workflows/ci.yml)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](LICENSE)

## Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Documentation and testing](#documentation-and-testing)
- [Security and privacy](#security-and-privacy)
- [Support and contributions](#support-and-contributions)
- [License](#license)

## Overview

The local component owns profiles, their version history, a searchable metadata catalog, publication policies, original author provenance and migration jobs. repository_multipicker exposes that library in native activity forms. Moodle’s File API stores all binaries; the plugin does not create another file store.

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
| Moodle | 4.5 only |
| Maintenance line | MOODLE_405_STABLE |
| Release / maturity | 4.5.0 / stable |
| PHP and database | Moodle’s requirements for the selected release; completed local checks use PHP 8.3 and PostgreSQL 17. |
| Companion | [repository_multipicker](https://github.com/didactika/moodle-repository_multipicker/tree/MOODLE_405_STABLE) from the same maintenance line. |

Moodle 4.5 and 5.3 have separate distributions. Other 5.x versions are not declared supported. This line retains the Moodle 4.5 AMD/Mustache implementation.

## Installation

Install the local companion first, then the repository component. Download the reviewed release ZIP and use Site administration → Plugins → Install plugins, or copy this repository into **local/multipicker** beneath the actual Moodle application directory. Moodle 5.3’s checked layout places that directory beneath public/.

Run the administrator upgrade screen or the native CLI upgrade:

```sh
php admin/cli/upgrade.php --non-interactive
```

Existing CourseBridge installations require a component rename: retain their records, policies and repository identities, migrate scheduled and queued task registrations, then remove the retired directories. Follow the detailed workspace rename procedure before upgrading an existing installation.

## Usage

1. Open Site administration → Plugins → Local plugins → MultiPicker.
2. Create and activate a profile defining related courses and supported public source areas.
3. Set the publication and author policy, then refresh the catalog through cron or course actions.
4. Enable the repository and use its native picker entry from an activity’s Add files action.
5. For site migrations, choose source/destination filters and activity correspondences, review the complete included set and queue the background job.

## Configuration

Guided profile forms expose course identification, grouping and dynamic named-capture filters. An advanced document editor remains available for supported rules. Migration correspondences belong to the migration wizard and select the source activity type separately from its matching rules. Site/course policy controls publication and authorship; retention settings bound inactive history.

Strict author protection refuses workflows where later native author changes cannot be prevented. Editable-with-provenance allows copying with the original declaration retained in the log. The original author and licence are preserved at copy time.

## Documentation and testing

Detailed explanations, the RUP UML catalogue, rendered diagrams and the exact validation matrix are maintained outside this plugin repository in the services workspace:

`projects/docs/in-progress/suite-coursebridge/moodle-local_multipicker/`

That local collection covers both maintenance lines and is not published online. This README, [CHANGELOG](CHANGELOG.md) and [LICENSE](LICENSE) remain in each distribution. The package ships English language strings only; Moodle language packs can provide translations.

Run the packaged PHPUnit and native Behat tests in a disposable Moodle checkout with both companions installed and independent test databases. Build frontend assets with the exact supported core tooling. Local validation distinguishes completed checks from pending remote pipelines; it does not claim untested runtime combinations.

## Security and privacy

Session-bound scopes, capabilities, live publication checks and native draft ownership are verified server-side. Private submissions, feedback and personal files are not public providers. Interactive selection is bounded to 200 source occurrences; oversized requests are refused rather than truncated. Migration destinations are native Folders; the plugin copies public files rather than recreating whole activities.

## Support and contributions

Report reproducible issues through [GitHub Issues](https://github.com/didactika/moodle-local_multipicker/issues), including the Moodle version, plugin release and steps without private data. Submit changes against the correct maintenance line and keep companion contracts aligned. Review [the changelog](CHANGELOG.md) and the documented test evidence before upgrading.

## License

Copyright © 2026 Didactika.org — Hector Arrechea <hectorlazaroarrechea@gmail.com>.

Licensed under the GNU General Public License v3 or later. See [LICENSE](LICENSE).
