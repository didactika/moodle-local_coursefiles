# MultiPicker — local_multipicker

Reusable teaching materials across related Moodle courses, with configurable grouping and edition labels.

This plugin is installed at `local/multipicker`. It owns relationship profiles, the material catalog, publication, author provenance, site/course policies and reviewed folder migrations. The separately installed **repository_multipicker** plugin exposes authorized materials inside Moodle's native file picker.

![Component boundaries](docs/diagrams/design/components.svg)

Existing CourseBridge installations use the [component rename procedure](docs/21-component-rename.md).

## Install and use

1. Install this repository into the Moodle application directory `local/multipicker` and run Moodle's plugin installation.
2. Open Site administration → Plugins → Local plugins → MultiPicker → Settings, then use the Profiles tab.
3. Activate a [profile](examples/institutional-profile.json), then refresh course materials from the course page or cron.
4. Install and enable repository_multipicker to select materials in native course editing forms.
5. Review the site author policy before copying. The strict default blocks copies through native editors that permit later author changes.

| Read about | Documentation |
|---|---|
| Teacher and administrator workflows | [User workflows](docs/02-vision-and-use-cases.md) |
| Current services and native integration | [Architecture](docs/03-architecture.md) |
| Source folders, page routes and template paths | [Source structure](docs/13-source-structure.md) |
| Guided fields, named capture filters and matching | [Configuration](docs/04-configurable-rules.md) |
| Installed tables and native storage | [Database](docs/05-data-model.md) |
| Reviewed folder migrations | [Migration](docs/06-migrations.md) |
| Site/course policy and author metadata | [Authorship](docs/09-authorship-policy.md) |
| Theme overrides and interface conventions | [Presentation](docs/11-presentation-and-theming.md) |
| Site actions and preserved installation records | [Log and upgrades](docs/12-log-and-upgrade.md) |
| Approved actor export and deletion | [Privacy](docs/14-privacy.md) |
| Internal file/folder batches into native drafts | [Draft batch service](docs/15-draft-batches.md) |
| Session-bound native control integration API | [Owning-form scope](docs/16-native-form-scope.md) |
| Visual mixed selection inside the native picker | [Folder selection](docs/17-folder-selection.md) |
| Public activity areas and community providers | [Public sources](docs/22-public-material-sources.md) |
| File activities and their main file | [File selection](docs/18-resource-selection.md) |
| Enter import from the native filemanager | [Activity entry](docs/19-activity-navigation.md) |
| Administration tabs and bulk preview screens | [Administration layout](docs/20-administration-layout.md) |
| History expiration and retained provenance | [Retention](docs/23-history-and-retention.md) |
| Tested core versions | [Compatibility](docs/10-compatibility-and-branches.md) |
| Actors, analysis, design and behavior | [UML catalogue](docs/rup/README.md) |
| Automated check results | [Validation](docs/07-validation-and-decisions.md) |

The interface uses native Moodle layouts, forms and theme-overridable Mustache templates. Cards and responsive tables expose file origins; migration reports show readable decisions and outcomes.

Version: **0.1.0-dev / alpha**. The documented workflows describe the current package. Native copies preserve the initial author/licence, but subsequent native edits remain possible under editable policy; strict policy refuses those copies. Bulk writes require maintenance mode and an exclusive administrative editing window. Migrations can create explicitly reviewed missing destination folders and optionally overwrite conflicting paths. Its installed schema has fourteen tables using `local_multipicker` or `local_multipicker_*`; native Moodle storage owns all binaries.

Required core targets: Moodle 4.5 and 5.3. See the exact [test evidence](docs/07-validation-and-decisions.md). License: GPL v3 or later.
