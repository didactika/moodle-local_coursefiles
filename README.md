<div align="center">

<img src="pix/icon.svg" width="96" alt="FileBank icon">

# FileBank for Moodle

*Find and reuse public course files across related courses*

[![Release](https://img.shields.io/github/v/release/didactika/moodle-local_filebank?filter=v*-m503&style=flat-square)](https://github.com/didactika/moodle-local_filebank/releases)
[![Moodle](https://img.shields.io/badge/Moodle-5.3-f98012?style=flat-square&logo=moodle&logoColor=white)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL_v3-blue?style=flat-square)](LICENSE)

[Overview](#overview) • [Installation](#installation) • [Usage](#usage) • [Configuration](#configuration) • [Troubleshooting](#troubleshooting)

</div>

FileBank (**local_filebank**) organizes public course files into a reusable library. Configurable profiles identify related courses, and teachers select material from those courses inside Moodle’s normal file picker. Administrators can also migrate complete sets of material between courses.

> [!IMPORTANT]
> **Install both [FileBank local](https://github.com/didactika/moodle-local_filebank) and [FileBank repository](https://github.com/didactika/moodle-repository_filebank).** Choose the same Moodle maintenance line for both plugins.

## Overview

Profiles define which courses belong together using named regular-expression captures. The same captures can become search filters, so institutions can describe their own groups without fixed year or semester rules.

| Feature | What it provides |
|---|---|
| Public files | Supported public material from Folder, File, Assignment handouts and other registered providers |
| File picker | Cards, list and lazy tree views with recursive multiple selection |
| Existing content | Material already used in the destination is excluded before browsing |
| Review | Resulting-file tree, removal checks and pencil dialogs for destination names |
| Profiles | Guided editing, import/export, retained versions, comparison and restoration |
| Migrations | A page with steps that checks what a migration would do before it is created and asks, in the last step, what happens with the files that are already different in the destination; a review of the files and folders, and background execution |
| Authorship | Original declarations preserved at copy time, with configurable protection and provenance |
| History | Bounded log, migration and inactive profile-version retention |

## Installation

**Requirements:** Moodle 5.3, PHP 8.3 or newer, and the database requirements of the selected Moodle release.

**From a release:** download the matching release ZIPs of both FileBank components, open **Site administration → Plugins → Install plugins**, upload the packages and follow the prompts.

**From Git:**

```bash
cd /path/to/moodle/public
git clone --branch main https://github.com/didactika/moodle-local_filebank.git local/filebank
git clone --branch main https://github.com/didactika/moodle-repository_filebank.git repository/filebank
cd ..
php admin/cli/upgrade.php --non-interactive
```

> [!IMPORTANT]
> Run [cron](https://docs.moodle.org/en/Cron) regularly to refresh the FileBank catalog, process queued migrations and clean expired history.

## Usage

1. Administrators open **Site administration → Plugins → Local plugins → FileBank**, create a profile and activate it.
2. Enable the FileBank repository and configure publication and authorship permissions.
3. Teachers open **Add files**, choose FileBank, filter the public library and select material.
4. Review the resulting files, import them and save the owning activity form.
5. For a site migration, open **Migrations**, choose the courses and the folder correspondences, check what it would do, say what happens with the files that are already different in the destination, create it and start it.

## Configuration

Settings are located at **Site administration → Plugins → Local plugins → FileBank**. Native tabs provide access to configuration, profiles, migrations and the log.

| Area | Purpose |
|---|---|
| Course profiles | Identify related courses and configure up to nine named-capture search filters |
| Publication | Choose which supported public file areas are available |
| Authorship | Preserve the original declaration, or block workflows that cannot enforce the selected protection |
| Migration window | Choose the courses, the folder correspondences, how sections match and what to do when a folder is missing or a different file is already there |
| Retention | Limit the lifetime of logs, migration history and inactive profile versions |

The repository instance’s visible name is also used as the browser heading. Editable-with-provenance permits copying while retaining the original author in the history. Strict protection blocks workflows where later native author edits cannot be prevented.

## Theming

The screens are drawn with the Bootstrap classes that Moodle themes already style (`btn`, `card`, `badge`, `table`, `nav-tabs`, `alert`, `pagination`, `form-control`, `form-select`), so a theme restyles them as it restyles the rest of the site and needs no knowledge of this plugin. Only the names of Bootstrap 5 are used, because Moodle 5 flags the ones of Bootstrap 4 as deprecated; on Bootstrap 4 the same classes get the same look from the plugin's own rules.

The plugin adds its design as rules scoped to `.filebank-ui` (and `.filebank-migration`), such as `.filebank-ui .btn-primary`, which beat the defaults of Bootstrap and whose specificity is only that of two classes. A theme changes one by declaring the property with `!important` on the Bootstrap class (for example `.btn-primary { border-radius: 0 !important; }`, which restyles every primary button of the site, this plugin's included) or with a selector of the same weight, such as `.filebank-ui .btn-primary`. Colours come from the variables of the theme (`--bs-primary`, `--bs-success`, `--bs-border-color`, ...), so changing the palette of the theme changes the plugin. Badges are tinted by the plugin from those colours and are the only element that does not take its colour straight from a Bootstrap class.

## Troubleshooting

| Problem | Check |
|---|---|
| No material appears | Check the active profile, matching course identifiers, publication rules and source permissions |
| An expected file is absent | It may already be used in the destination, or the native control may reject its type |
| Locked material cannot be copied | The selected author policy requires protection that the workflow cannot enforce |
| Nothing would be migrated | Edit its parameters: the migration is worked out again where it is, and what the source does not have is not an error |
| A queued migration does not run | Check cron and the scheduled/ad hoc task log |

## Getting help

Open an [issue](https://github.com/didactika/moodle-local_filebank/issues) with your Moodle and PHP versions, the source activity, the destination form and the expected and actual behavior.
