# Administration and review screens

CourseBridge opens from **Site administration → Plugins → Local plugins → CourseBridge**. Only the first authorized page appears in the administration menu; Settings is the entry for a site administrator, while a delegated manager enters through their own authorized page. Settings, Profiles, Migrations and Log share permission-aware page tabs. They are regular Moodle pages and remain usable without custom JavaScript. Each page independently checks its system capability; hiding a tab is not its authorization boundary.

![Administration pages](diagrams/design/administration-pages.svg)

## Settings

`admin/settings.php` edits the existing site author policy, course override policy and missing-author policy through a native Moodle form. Existing values and strict defaults are preserved. Only users with `moodle/site:config` can enter or save it. Changes appear in the native configuration log and the CourseBridge Log tab.

## Profiles

Moodle 4.5 does not build the Local plugins category for delegated users without site configuration access. CourseBridge registers their authorized entry in the administration root instead; it does not grant site configuration capability.

`admin/profiles.php` lists named profile cards, status and active revision. Creating or editing opens `admin/profile.php`; the large rule editor is absent from the list. **Preview rules** validates the submitted JSON and describes relationship, section, source/destination and duplicate rules without writing or activating a revision. **Save profile** activates a validated revision and returns to the list. Cancel discards the form values. A delegated `manageprofiles` role can use these pages without site configuration access.

The editor retains the full generic JSON schema. Its summary describes configuration meaning; it is not a simulation of course matching or file copies. Only the migration preview evaluates indexed course pairs and actual source/destination files.

## Migrations

`admin/migration.php` contains profile selection, the active rule summary, edition fields with indexed suggestions and the necessary section-layout confirmation. Recent stored previews are available in a disclosure panel. Changing the selected profile loads its own active revision before a preview can be created.

**Create preview** stores immutable decisions and opens `admin/migration_review.php`. The review describes the exact original profile revision even if the active profile subsequently changes. Whole-plan counts distinguish copies, existing content and blocked operations. Decision links filter a bounded page of 50 files, grouped by source course and destination folder. Previous/Next links retain the filter. The summary counts and overall progress always describe the complete plan.

![Migration review flow](diagrams/behavior/processes/administration-migration.svg)

Execution sits after the reviewed files. A separate confirmation, execution capability, reviewable plan, correct digest and site maintenance mode are required. Maintenance mode is never enabled automatically. Source/destination revalidation and exclusive editing requirements remain enforced by the migration service. Queued/running plans show progress, refresh and authorized cancellation. Changing configuration creates a new preview; it does not rewrite a stored plan.

Legacy `admin/migration.php?planid=...` links redirect to the review page. Legacy profile edit links likewise redirect to the separate editor.

## Course pages and themes

Course material browsing and course author policy share a single course navigation entry and their own permission-filtered tabs; site administration tabs are not displayed there. The catalog keeps filename and edition visible, with further filters, publication and maintenance controls in disclosures. The native file picker retains its embedded batch workflow.

Templates are organized under `local/navigation`, `local/profile`, `local/migration`, `local/catalog` and `local/log`. Native forms and shared Bootstrap classes retain the active Moodle theme's controls, spacing and focus behavior. There is no added framework, stylesheet or client-side tab controller. See [presentation and theme overrides](11-presentation-and-theming.md).

References: [Moodle 4.5 Navigation API](https://moodledev.io/docs/4.5/apis/core/navigation), [administration external pages](https://phpdoc.moodledev.io/4.5/d9/dd6/classadmin__externalpage.html), [Moodle 5.3 Forms API](https://moodledev.io/docs/5.3/apis/subsystems/form).
