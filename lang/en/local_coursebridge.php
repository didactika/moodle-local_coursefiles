<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * User-facing language strings.
 *
 * @package local_coursebridge
 * @copyright 2026 CourseBridge contributors
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'CourseBridge';
$string['settings'] = 'Material policies';
$string['profiles'] = 'Relationship profiles';
$string['dashboard'] = 'Course materials';
$string['profilejson'] = 'Profile configuration (JSON)';
$string['profile'] = 'Profile';
$string['saveprofile'] = 'Validate and activate revision';
$string['preview'] = 'Preview migration';
$string['sourceedition'] = 'Source edition';
$string['targetedition'] = 'Target edition';
$string['layoutconfirmed'] = 'I reviewed the source and target section layout';
$string['execute'] = 'Execute reviewed plan';
$string['cancelrun'] = 'Cancel run';
$string['publish'] = 'Publish module';
$string['revoke'] = 'Revoke publication';
$string['cmid'] = 'Course module ID';
$string['coursepolicy'] = 'Course material policy';
$string['author_policy'] = 'Author policy';
$string['author_policy_desc'] = 'Locked authors fail closed in native workflows without proven pre-save enforcement. Editable mode preserves initial metadata and records provenance but allows later native edits.';
$string['preserve_locked'] = 'Preserve and lock declared author';
$string['editable_with_provenance'] = 'Allow edits with original provenance';
$string['author_course_override'] = 'Course overrides';
$string['author_course_override_desc'] = 'Courses may inherit or tighten the site policy; they cannot relax it.';
$string['disabled'] = 'Disabled';
$string['tighten_only'] = 'Allow stricter course policy';
$string['inherit'] = 'Inherit site policy';
$string['author_missing_policy'] = 'Missing declared author';
$string['author_missing_policy_desc'] = 'Never attribute an undeclared material to the importing teacher automatically.';
$string['preserve_unknown'] = 'Keep author undeclared';
$string['block_reuse'] = 'Block reuse until corrected';
$string['maintenance_required'] = 'Bulk writes require site maintenance mode and an exclusive administrative editing window.';
$string['lockedunsupported'] = 'This workflow cannot prevent later native author edits. Locked material cannot be copied here. See the authorship coverage documentation.';
$string['invalidprofile'] = 'Invalid or unsupported configuration. Check the JSON schema, declared captures and supported options.';
$string['ambiguousprofile'] = 'Several course parsers resolve conflicting identities.';
$string['regexfailed'] = 'The pattern is invalid or exceeds the configured PCRE work limit.';
$string['invalidpolicy'] = 'The requested course policy is not permitted by the site.';
$string['missingauthor'] = 'This material has no declared author and site policy blocks reuse.';
$string['materialunavailable'] = 'The material is unavailable or outside your current reuse authority.';
$string['placementambiguous'] = 'The destination section or folder is missing, ambiguous or unsupported.';
$string['layoutrequired'] = 'Choose different nonempty edition labels and confirm ordinal section layout when applicable.';
$string['planstale'] = 'The reviewed plan no longer matches current course, file, attribution or policy state.';
$string['verificationfailed'] = 'The saved native file does not match the reviewed material.';
$string['lockfailed'] = 'The destination or metadata is busy. Retry later.';
$string['invalidcursor'] = 'Invalid catalog cursor.';
$string['targetrequired'] = 'Open this repository from a course editing context.';
$string['noprofile'] = 'No enabled relationship profile exists. Ask an administrator to configure CourseBridge.';
$string['reconcilecatalog'] = 'Reconcile supported course material catalog';
$string['notice'] = 'Development build. Author locks require a verified destination integration; native workflows are currently available only under editable-with-provenance policies.';
$string['privacy_metadata'] = 'CourseBridge stores rule revisions, publication authority, declared attribution, copy lineage, migration attempts and scoped log history.';
$string['privacy_userid'] = 'The user who configured, published, imported or executed the action.';
$string['privacy_details'] = 'Configuration, declared attribution and structured action outcomes.';
$string['reindex'] = 'Refresh course catalog';
$string['log'] = 'Recent log';
$string['state'] = 'State';
$string['decision'] = 'Decision';
$string['source'] = 'Source';
$string['destination'] = 'Destination';
$string['report'] = 'Migration report';
$string['materials'] = 'Materials';
$string['nextpage'] = 'Next page';
$string['none'] = 'No matching permitted materials.';
$string['revisionactivated'] = 'Profile revision activated. Refresh the catalog or wait for reconciliation.';
$string['policyupdated'] = 'Course policy saved.';
$string['published'] = 'Publication state updated.';
$string['coursebridge:browse'] = 'Browse related materials';
$string['coursebridge:reuse'] = 'Reuse related materials';
$string['coursebridge:publish'] = 'Publish historical materials';
$string['coursebridge:manageprofiles'] = 'Manage relationship profiles';
$string['coursebridge:planmigration'] = 'Preview bulk migrations';
$string['coursebridge:executemigration'] = 'Execute and cancel reviewed migrations';
$string['coursebridge:viewlog'] = 'Read CourseBridge log';
$string['coursebridge:managecoursepolicy'] = 'Manage inherited course material policy';
$string['coursebridge:correctattribution'] = 'Correct attribution through an authorized integration';
$string['catalogintro'] = 'Find teaching files from related courses. Use the filters to narrow the results.';
$string['originhelp'] = 'Each file appears once. Its rows identify the available courses, editions and original authors.';
$string['emptyhelp'] = 'Try a different edition or remove filters. Only materials you may access are shown.';
$string['clearfilters'] = 'Clear filters';
$string['pagination'] = 'Material pages';
$string['filtermaterials'] = 'Filter materials';
$string['filenamefilter'] = 'File name';
$string['editionfilter'] = 'Edition';
$string['modulefilter'] = 'Source activity';
$string['filetypefilter'] = 'File type';
$string['allmodules'] = 'All supported activities';
$string['alltypes'] = 'All file types';
$string['plaintext'] = 'Plain text';
$string['origincount'] = '{$a} available origins';
$string['originscaption'] = 'Available origins of {$a}';
$string['noedition'] = 'Without edition';
$string['unknownauthor'] = 'Author undeclared';
$string['catalogmaintenance'] = 'Update available materials';
$string['refreshhelp'] = 'Request an update when course materials have changed. You can continue browsing.';
$string['refreshqueued'] = 'Catalog update requested.';
$string['publicationtitle'] = 'Share materials with related courses';
$string['publicationhelp'] = 'Choose a folder or file activity to allow reuse in related courses. Revoking publication stops future reuse; existing copies remain.';
$string['publicationmodule'] = 'Folder or file activity';
$string['nopublicationmodules'] = 'This course has no supported folder or file activities.';
$string['publishedlabel'] = 'Published';
$string['policyintro'] = 'The course inherits the site author policy. It can apply a stricter policy when the site permits it.';
$string['effectivepolicy'] = 'Effective policy';
$string['lockedpolicyhelp'] = 'Copying is blocked because native Moodle editors allow later author changes.';
$string['editablepolicyhelp'] = 'Copies keep the original author and licence. Later changes through Moodle editors are permitted.';
$string['policymanagedbysite'] = 'The site administrator manages this policy.';
$string['backtomaterials'] = 'Back to materials';
$string['migrationintro'] = 'Choose the source and destination editions. Review the resulting file list before copying into existing folders.';
$string['planreference'] = 'Plan reference';
$string['reviewissues'] = 'Issues to resolve before copying';
$string['reviewbeforeexecute'] = 'Check the destination folders and each decision below. Copies require site maintenance mode and an exclusive administrative editing window.';
$string['newpreview'] = 'Create another preview';
$string['runreference'] = 'Run {$a}';
$string['migrationtablecaption'] = 'Reviewed files and current outcomes';
$string['nomigrationoperations'] = 'No files matched the selected editions and placement rules.';
$string['metric_copy'] = 'Files to copy';
$string['metric_skip'] = 'Already available';
$string['metric_blocked'] = 'Blocked files';
$string['progresslabel'] = '{$a->done} of {$a->total} file decisions processed';
$string['runprocessed'] = '{$a} file decisions processed in this run';
$string['planprogress'] = 'Overall plan progress';
$string['sectionnumber'] = 'Section {$a}';
$string['profilesintro'] = 'Profiles define which courses are related and how materials are matched.';
$string['revision'] = 'Active revision';
$string['newprofile'] = 'New profile';
$string['profileeditor'] = 'Profile configuration';
$string['operationfailed'] = 'The operation could not be completed. Review the course and material permissions.';
$string['ambiguous_course_pair'] = 'There is no unique source and destination course pair for the selected editions.';
$string['outcome_reviewable'] = 'Ready for review';
$string['outcome_blocked'] = 'Blocked';
$string['outcome_copy'] = 'Copy';
$string['outcome_skip_identical'] = 'Skip: identical file';
$string['outcome_skip_content'] = 'Skip: content already available';
$string['outcome_blocked_content'] = 'Blocked: different content at this path';
$string['outcome_blocked_attribution'] = 'Blocked: conflicting author or licence';
$string['outcome_blocked_policy'] = 'Blocked by author policy';
$string['outcome_pending'] = 'Pending';
$string['outcome_done'] = 'Processed';
$string['outcome_queued'] = 'Queued';
$string['outcome_running'] = 'In progress';
$string['outcome_completed'] = 'Completed';
$string['outcome_partial'] = 'Finished with blocked files';
$string['outcome_cancelled'] = 'Cancelled';
$string['outcome_copied'] = 'Copied';
$string['outcome_already_present'] = 'Already available';
$string['outcome_enabled'] = 'Enabled';
$string['outcome_disabled'] = 'Disabled';
$string['logintro'] = 'Actions performed in CourseBridge. Use the reference from a migration or profile to find its records.';
$string['logaction'] = 'Action';
$string['logallactions'] = 'All actions';
$string['logreference'] = 'Reference';
$string['logunknownuser'] = 'System or removed user';
$string['logempty'] = 'No records match these filters.';
$string['logolder'] = 'Older records';
$string['logaction_profile_activated'] = 'Profile activated';
$string['logaction_course_policy_changed'] = 'Course author policy changed';
$string['logaction_publication_granted'] = 'Material sharing enabled';
$string['logaction_publication_revoked'] = 'Material sharing disabled';
$string['logaction_migration_previewed'] = 'Migration preview created';
$string['logaction_migration_queued'] = 'Migration queued';
$string['logaction_migration_blocked'] = 'Migration blocked';
$string['logaction_migration_batch'] = 'Migration batch processed';
$string['invalidselection'] = 'Select available files or non-empty folders, up to 200 files per batch.';
$string['invaliddraft'] = 'The destination draft directory is unavailable for your account.';
$string['batchlimit'] = 'The selection exceeds the destination control limits or contains an unsupported file type or directory.';
$string['batchconflict'] = 'The selection conflicts with existing files or changed after review. Review it again before copying.';
$string['formscopeunavailable'] = 'This form connection is unavailable or has expired. Reopen the material selection from the form.';
$string['batchheading'] = 'Reuse related materials';
$string['batchintro'] = 'Search earlier editions, choose files or folders, and review the batch. Added materials remain in this form draft until you save the activity.';
$string['batchsearch'] = 'Search materials';
$string['batchselection'] = 'Files and folders';
$string['batchselectionhelp'] = 'Folders include all eligible files and subfolders, including files outside the search filters. File choices show one labelled origin per content group; filter by edition or course to choose another origin. Selections remain when searching or changing pages.';
$string['batchnext'] = 'More materials';
$string['batchreview'] = 'Review selection';
$string['batchapply'] = 'Add reviewed materials';
$string['batchadded'] = 'Materials added to the draft. Save the activity to keep them.';
$string['batchmainfilehelp'] = 'Added files do not replace the current main file. Use the native file manager action "Set main file" to choose which file this activity opens.';
$string['batchfile'] = 'File';
$string['batchfolder'] = 'Folder';
$string['batchretained'] = 'Selected item {$a} (outside current results)';
$string['batchresult'] = 'Resulting draft files:';
$string['batchpath'] = 'Destination path';
$string['batchdecision'] = 'Decision';
$string['batchlimited'] = 'Folder choices are limited on this page. Narrow the edition or course filters.';
$string['batchempty'] = 'No materials match these filters. Existing selections remain available for review.';
$string['shortnamefilter'] = 'Course shortname';
