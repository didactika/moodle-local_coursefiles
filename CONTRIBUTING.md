# Contributing

Use Moodle coding conventions, English code/comments/documentation and English source language strings. Business tables belong to local_filebank with the full component prefix. Services decide authorization; renderers and templates own presentation.

Install into a disposable Moodle checkout and initialize its isolated PHPUnit database. Run from the core root, adjusting the application path:

```sh
vendor/bin/phpunit --test-suffix _test.php APPLICATION/local/filebank/tests
```

Check PHP syntax, Moodle coding standards, XMLDB validation, template escaping and the relevant functional behavior on Moodle 4.5 and 5.3. Detailed documentation and PlantUML sources are maintained in the central services workspace under projects/docs/in-progress/suite-filebank/moodle-local_filebank/. Update the explanations and regenerate adjacent SVG previews when behavior changes.

Theme changes use the documented component template names and stable region classes. Preserve field names, server-side capability checks, escaping and native form contracts.

Document behavior together with its code and current limitations. Workspace development instructions are in the root AGENTS.md; planning and unimplemented specifications are not package documentation. License: GPL v3 or later.

Frontend features separate controller initialization, scoped actions, listeners, selectors and backend repository access. Preserve native entry exports and the same PHP/session contracts on both lines. Moodle 5.3 uses strict TypeScript ESM and native React; keep those sources and generated assets off the 4.5 distribution.

Edit styles.scss and scss/ component sources, then run `node tools/build-styles.mjs /path/to/moodle`. CI checks generated CSS using `--check`. The 5.3 pipeline also generates core aliases, checks all plugin sources with `npx tsc --project public/local/filebank/tsconfig.json`, and runs the native ESM build.

## The migration screens

The migration screens are TypeScript that is written once, in the Moodle 5.3 line (`js/esm/src/Migration.tsx` and `js/esm/src/migration`). This line keeps a copy in `js/src` and builds it, together with Preact, into the AMD module `amd/src/migration_app.js`, so do not edit either of them here. To bring a change over from `main`:

```
node tools/sync-migration-ui.mjs <worktree of main>
npm ci
node tools/build-migration.mjs
```

Then minify the module with `grunt amd` as with any other AMD module. CI runs `node tools/build-migration.mjs --check`, which fails when the module is not what `js/src` produces.
