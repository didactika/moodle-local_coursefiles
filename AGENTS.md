# Plugin instructions

This is a standalone Moodle plugin repository. Use the workspace conventions when working in the shared workspace.

- Required compatibility targets: Moodle 4.5 and Moodle 5.3. Verify each core's APIs/build/layout and relevant install, upgrade, functional and security behavior before declaring support.
- Preserve the Moodle 4.5 implementation. Custom AMD/frontend work requires a distinct Moodle 5 maintenance branch; adapt to React with TSX/TS when necessary. Do not force React into server-only/native-PHP work or infer every Moodle 5.x release is compatible.
- Use Moodle component names, lowercase class/method/variable conventions, XMLDB/DML, capability checks, language strings and native file/module interfaces. Business tables belong exclusively to local_coursebridge with its full prefix.
- Documentation, PlantUML and code comments are English; user communication is Spanish. Embed diagrams near their explanations and keep source/SVG siblings current.
- Public documentation and UML describe implemented behavior and actual constraints. Keep iteration plans and unimplemented specifications outside plugin repositories in the suite engineering-notes directory. Strict author protection fails closed when pre-commit/later-editor protection is unproven.
- Use a dedicated branch. Never commit or push for the user; provide relevant commands after changes.
