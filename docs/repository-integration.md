# Service contract consumed by repository_coursebridge

The companion repository is a presentation and native draft adapter. It depends on local_coursebridge, owns no catalog or business tables, and installs at `repository/coursebridge`. Its own repository documents its UI and distribution.

![Native picker contract](diagrams/design/sequences/picker-integration.svg)

Use `catalog::search(targetcourse, profileid, filters, after)` for authorized grouped cards. Use `material_source::resolve(pathnamehash, targetcourse, profileid)` immediately before selection/copy. Use `author_policy::require_native_copy()` to fail closed when source/target/origin protection cannot be enforced. `author_policy::capture()` retains the chosen declaration independently of byte grouping.

The repository decodes a locator/profile token, revalidates it, and produces Moodle's packed internal-file reference. The core source-key/session validation is retained. Its copy adapter repeats authorization, overwrites submitted initial author/license with the source's values, captures provenance and delegates to core draft copying.

The resolved target context comes from the native repository instance, not a requested arbitrary course ID. Shared business decisions stay server-side; the template contract controls presentation.
