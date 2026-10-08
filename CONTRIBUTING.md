# Contributing

Use Moodle coding conventions, English code/comments/documentation and English/Spanish language strings. Business tables belong to local_coursebridge with the full component prefix. Services decide authorization; renderers and templates own presentation.

Install into a disposable Moodle checkout and initialize its isolated PHPUnit database. Run from the core root, adjusting the application path:

```sh
vendor/bin/phpunit --test-suffix _test.php APPLICATION/local/coursebridge/tests
```

Check PHP syntax, Moodle coding standards, XMLDB validation, template escaping and the relevant functional behavior on Moodle 4.5 and 5.3. Diagrams describe executable behavior and are regenerated locally from their versioned PlantUML sources.

Theme changes use the documented component template names and stable region classes. Preserve field names, server-side capability checks, escaping and native form contracts.

Document behavior together with its code and current limitations. Development instructions are in AGENTS; planning and unimplemented specifications are not package documentation. License: GPL v3 or later.
