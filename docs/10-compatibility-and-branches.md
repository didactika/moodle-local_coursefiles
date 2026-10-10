# Compatibility

Required targets: **Moodle 4.5 and Moodle 5.3**. The same PHP/native template package is tested on both cores. A small core-built AMD adapter connects the inherited native filemanager and documented embedded repository chooser. The Moodle 5 maintenance distribution is kept separately. The interactive picker/filemanager remain core widgets and the browser uses native templates. The Moodle 5.3 navigation delegates to the exact core secondarymoremenu template, which loads its own React navigation. No plugin-owned TSX component is needed to replace that native integration. No React/ESM imports are added to the Moodle 4.5 distribution.

| Tested core | Application layout | PHP / database |
|---|---|---|
| Moodle 4.5.13, build 20260810 | Plugins under the core application root | PHP 8.3.33 / PostgreSQL 17.10 |
| Moodle 5.3, build 20261005 | Plugins under the checkout's `public/` application directory | PHP 8.3.33 / PostgreSQL 17.10 |

Installation paths are `local/multipicker` and `repository/multipicker` relative to the actual Moodle application directory. The site config arrangement belongs to the core checkout.

Native forms, repository drafts, folder save contracts, templates, permission checks and isolated XMLDB installation are exercised on the required targets. The [validation record](07-validation-and-decisions.md) states the exact evidence and its limits. Support for other 5.x releases is not inferred.

Presentation follows the active Moodle theme. The templates use shared native form/card/table classes and accessible HTML; version-specific visual utilities are not required for controls or decisions. [Theme overrides](11-presentation-and-theming.md) document the current extension points.

Maintenance branches for this delivery: `feat/multipicker-workflows-45` and `feat/multipicker-workflows-5`. The Moodle 5 artifact declares Moodle 5.3 as its minimum; use the Moodle 4.5 artifact for that release. Shared PHP, database, capability and draft contracts are aligned. The administration and browser modules are built against each exact core separately.

The 5.3-specific secondary navigation partial remains off the 4.5 line. Five plugin AMD modules (browser, editor_import, administration, row_editor, review) are built through each exact core Grunt pipeline. Shared PHP contracts, permissions, schema and user behavior remain aligned.

Native select markup is an explicit presentation adapter: the 4.5 templates and dynamic row editor use custom-select/custom-select-sm, while the 5.3 distribution uses form-select/form-select-sm. Combining both classes would activate Moodle 5.3's deprecated Bootstrap 4 styles and is avoided. The typed/server contracts are unchanged.
