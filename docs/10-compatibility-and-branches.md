# Compatibility

Required targets: **Moodle 4.5 and Moodle 5.3**. The same PHP/native template package is tested on both cores. There is no custom AMD, React, TS or TSX bundle.

| Tested core | Application layout | PHP / database |
|---|---|---|
| Moodle 4.5.13, build 20260810 | Plugins under the core application root | PHP 8.3.33 / PostgreSQL 17.10 |
| Moodle 5.3, build 20261005 | Plugins under the checkout's `public/` application directory | PHP 8.3.33 / PostgreSQL 17.10 |

Installation paths are `local/coursebridge` and `repository/coursebridge` relative to the actual Moodle application directory. The site config arrangement belongs to the core checkout.

Native forms, repository drafts, folder save contracts, templates, permission checks and isolated XMLDB installation are exercised on the required targets. The [validation record](07-validation-and-decisions.md) states the exact evidence and its limits. Support for other 5.x releases is not inferred.

Presentation follows the active Moodle theme. The templates use shared native form/card/table classes and accessible HTML; version-specific visual utilities are not required for controls or decisions. [Theme overrides](11-presentation-and-theming.md) document the current extension points.
