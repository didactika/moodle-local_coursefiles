# User workflows

A configurable **family** relates courses. An optional **edition** labels a year, term, cohort or other revision. An **occurrence** is one native file usage; a grouped search result combines identical bytes without merging original author declarations.

![System context](diagrams/requirements/system-context.svg)

## Find and reuse materials

Open a course editing file picker and select **MultiPicker**. Choose a relationship profile, filename, configured capture values, course shortname, activity or file type. Identical content appears once. Each selection retains an authorized source occurrence and its original author/licence.

![Teacher goals](diagrams/requirements/reuse-use-cases.svg)

![Native picker sequence](diagrams/design/sequences/picker-integration.svg)

The course Materials page uses the same authorized catalog with filters, grouped cards and origin tables. Only permitted origins contribute to names and counts. The importing account does not become the declared author.

## Select several files and folders

For several files and folders together, open the native Add files control and choose **MultiPicker**. Tick file/folder cards in the embedded picker, review the selection, and import into the current draft. Folder content editors stay on the separate Edit page. Native Save/Cancel and File main-file selection remain in control. See [Folder selection](17-folder-selection.md) and [File selection](18-resource-selection.md).

![Mixed-selection conversation](diagrams/requirements/use-case-details/uc10-select-materials.svg)

## Eligible teaching content

Public teaching content means visible, unrestricted course material eligible for the configured reuse policy. It does not mean anonymous web publication. Supported providers expose Folder/File/Page/Book content, Assignment teaching attachments and declared public activity introductions. Profile checkboxes enable those areas. Hidden or restricted contexts, aliases, private drafts, submissions, feedback, backups and undeclared areas are excluded. See [public source contracts](22-public-material-sources.md).

Source-access-only profiles require current access to the source. Explicit family-publication profiles additionally accept grants on eligible modules. Revocation stops future reuse; existing copies remain independent.

![Publication and policy goals](diagrams/requirements/policy-use-cases.svg)

## Review a migration

The wizard separates profile, source/destination, review and execution. Select an edition or any configured capture filter as the migration dimension, then choose different source and destination values. The preview resolves every unique course pair and matching existing folder. Its review shows 50 file decisions per page with search, per-file checkboxes and whole-plan inclusion controls. Exclusions persist across pages; queueing processes all included matches. Source materials and target settings are preserved. Execution revalidates the reviewed plan and reports copied, skipped and blocked outcomes.

![Administrative goals](diagrams/requirements/migration-use-cases.svg)

Site policy and permitted course overrides control author protection. A course inherits or tightens the site policy. Native editors allow later author changes, so strict protection rejects these copies; editable-with-provenance preserves the initial declaration.

The [use-case catalogue](rup/requirements/use-cases/README.md) describes these supported workflows. Custom uploaders that bypass the Repository API are outside this plugin's integration contract.
