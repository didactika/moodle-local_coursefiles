# Source structure

Moodle discovers plugin metadata, callbacks and repository entry classes at their standard locations. Those entry files stay at the plugin root. Business services use namespaced subdirectories under `classes/local/`; the directory and namespace match.

## Local plugin layout

```text
local/coursebridge/
├── admin/                  profiles.php, migration.php, log.php
├── course/                 index.php, policy.php
├── classes/
│   ├── local/
│   │   ├── catalog/        catalog, indexer, material_source
│   │   ├── profile/        profiles, profile_config
│   │   ├── policy/         author_policy
│   │   ├── migration/      planner, executor, placement, folder adapter, decisions
│   │   ├── selection/      material_selection, draft_import (internal draft batches)
│   │   └── logging/        log
│   ├── form/               native Moodle forms
│   ├── output/             shared view, renderer and display labels
│   ├── privacy/            Moodle Privacy API provider
│   └── task/               Moodle task entry classes
├── templates/local/
│   ├── catalog/            index, filters, material_card, publication
│   ├── profile/            list
│   ├── policy/             course
│   ├── migration/          report
│   └── log/                index
├── db/                     schema, upgrades, capabilities, task/event definitions
├── cli/                    reindex.php
└── lib.php, settings.php, version.php
```

![Source responsibilities](diagrams/design/source-structure.svg)

For example, `classes/local/catalog/indexer.php` defines `local_coursebridge\\local\\catalog\\indexer`. Cross-domain dependencies use explicit imports. The root observer, task class names and Privacy API provider retain their Moodle entry contracts.

## Page routes

| Purpose | Canonical route |
|---|---|
| Course catalog | `/local/coursebridge/course/index.php` |
| Course policy | `/local/coursebridge/course/policy.php` |
| Relationship profiles | `/local/coursebridge/admin/profiles.php` |
| Migration administration | `/local/coursebridge/admin/migration.php` |
| Action log | `/local/coursebridge/admin/log.php` |

The five previous root page URLs remain authenticated forwarding entry scripts. They include the actual controller, preserving request parameters and POST handling. Controllers retain their course/system capability and session-key checks. New navigation links and form actions use the canonical routes.

## Templates and theme overrides

Templates use a `local/` first subdirectory, following Moodle's component namespace rules. The view's logical names remain `catalog`, `policy`, `profile_list`, `migration_report` and `log`; `output/view` maps them to the organized identifiers documented in [presentation](11-presentation-and-theming.md).

Existing custom theme overrides must use the new paths, for example:

```text
theme/mytheme/templates/local_coursebridge/local/catalog/material_card.mustache
```

Replace the plugin code as a complete package so removed class/template files do not remain as stale copies, then run Moodle's normal plugin upgrade and cache purge. Business table names and stored task class names are unchanged.

References: [Moodle templates and subdirectories](https://moodledev.io/docs/4.5/guides/templates), [autoloaded common files](https://moodledev.io/docs/4.5/apis/commonfiles).

