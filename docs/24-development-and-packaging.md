# Development and packaging

The GitHub workflows derive from [Didactika's Moodle plugin template](https://github.com/didactika/template-moodle-plugin). CI reads each distribution's exact supported range from version.php. The Moodle 4.5 line declares [405, 405]; the Moodle 5.3 line declares [503, 503]. No intermediate release is implied. Duplicate range endpoints are removed before building the PHP/database matrix.

The workflow accepts pull requests to main, master and the support/moodle-* lines, as well as manual runs. Publication is a separate manually requested workflow; committing or pushing a version does not publish a release automatically. Release numbers must be distinct between maintenance lines.

The local and repository packages have separate repositories. Repository CI installs the matching local maintenance line before installing repository_multipicker. Local CI installs the repository integration for its native picker acceptance tests. Both repositories and maintenance lines must exist before these workflows can resolve their test dependencies. Local checks recorded in [validation](07-validation-and-decisions.md) do not establish that a new GitHub workflow has passed.

## Native builds

Build the local plugin through the supported core checkout. Moodle 4.5 uses local/multipicker; the checked Moodle 5.3 layout uses public/local/multipicker:

```sh
# From the Moodle 4.5 checkout root.
npx grunt amd --root=local/multipicker

# From the Moodle 5.3 checkout root.
npx grunt amd --root=public/local/multipicker
```

Keep the resulting amd/build assets with their own distribution. The repository consumes the local adapter and does not ship a second frontend. The exact Moodle 5.3 build also generates AMD type declarations; that tooling is not copied onto the Moodle 4.5 line.

PHPUnit 9 on Moodle 4.5 reads the test coverage PHPDoc annotations. The Moodle 5.3 test distribution adds native PHPUnit 11 coverage attributes to avoid deprecated metadata. The production services and test expectations remain aligned.

Each package archive excludes GitHub configuration and development-only VCS files through .gitattributes. Plugin installation paths remain local/multipicker and repository/multipicker, independent of their GitHub repository names.
