# Contributing

Use Moodle coding conventions, English code/comments/documentation and English source language strings. Business tables belong to local_multipicker with the full component prefix. Services decide authorization; renderers and templates own presentation.

Install into a disposable Moodle checkout and initialize its isolated PHPUnit database. Run from the core root, adjusting the application path:

```sh
vendor/bin/phpunit --test-suffix _test.php APPLICATION/local/multipicker/tests
```

Check PHP syntax, Moodle coding standards, XMLDB validation, template escaping and the relevant functional behavior on Moodle 4.5 and 5.3. Detailed documentation and PlantUML sources are maintained in the central services workspace under projects/docs/in-progress/suite-coursebridge/moodle-local_multipicker/. Update the explanations and regenerate adjacent SVG previews when behavior changes.

Theme changes use the documented component template names and stable region classes. Preserve field names, server-side capability checks, escaping and native form contracts.

Document behavior together with its code and current limitations. Workspace development instructions are in the root AGENTS.md; planning and unimplemented specifications are not package documentation. License: GPL v3 or later.
