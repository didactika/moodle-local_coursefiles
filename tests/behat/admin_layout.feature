@local @local_coursefiles @coursefiles_admin @javascript
Feature: Clear administration tabs and immutable migration previews
  As a CourseFiles administrator
  I need separate configuration, review and execution screens

  Background:
    Given the following "local_coursefiles > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Profile bulk icons are hidden until profiles are selected
    Given I log in as "admin"
    When I open CourseFiles administration "profiles"
    Then CourseFiles profile bulk actions should be "hidden"
    When I click on "input[name='profiles[]']" "css_element"
    Then CourseFiles profile bulk actions should be "visible"
    And I capture CourseFiles administration "bulk-selected"
    When I click on "input[name='profiles[]']" "css_element"
    Then CourseFiles profile bulk actions should be "hidden"
    When I click on "[data-coursefiles-selectall]" "css_element"
    And I remember the selected CourseFiles profile rows
    And I press "Disable selected"
    Then I should see "Disabled" in the ".coursefiles-profiles table" "css_element"
    And CourseFiles profile bulk actions should be "visible"
    And CourseFiles should retain its selected profile rows
    And "button[value='disable']" "css_element" should not be visible
    When I press "Enable selected"
    Then I should see "Enabled" in the ".coursefiles-profiles table" "css_element"
    And I capture CourseFiles administration "bulk-icons"

  Scenario: Native settings save without leaving the administration tabs
    Given I log in as "admin"
    When I open CourseFiles administration "category"
    Then I should see "Profiles" in the "[data-region='coursefiles-navigation']" "css_element"
    And I should see "Migrations" in the "[data-region='coursefiles-navigation']" "css_element"
    And I should see "Log" in the "[data-region='coursefiles-navigation']" "css_element"
    When I press "Save changes"
    Then I should see "Changes saved"
    And I capture CourseFiles administration "settings"

  Scenario: Previewing edited profile rules keeps the saved profile unchanged
    Given I log in as "admin"
    When I open CourseFiles administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "Name" to "Unsaved profile name"
    And I press "Preview rules"
    Then I should see "Nothing has been saved or activated"
    And I should not see "Rules in use"
    And I should see "Unsaved profile name"
    And I capture CourseFiles administration "profile-editor"
    When I press "Cancel"
    Then I should see "Migration acceptance"
    And I should not see "Unsaved profile name"
    And I capture CourseFiles administration "profiles"

  Scenario: A stored migration preview groups files and exposes decision filters
    Given I log in as "admin"
    When I open CourseFiles administration "migration"
    Then I should see "Migrations"
    When I follow "New migration"
    And I press "Continue to source and destination"
    And I set the field "sourcefilters[edition]" to "2025"
    And I set the field "targetfilters[edition]" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I press "Create preview"
    Then I should see "CB2025"
    And I should see "CB2026"
    And I should see "lesson.txt"
    And I should see "Original teacher"
    When I follow "Continue to execution"
    Then I should see "Files are processed in the background without enabling site maintenance"
    When I follow "Back to review"
    And I capture CourseFiles administration "migration-review"
    When I follow "Already available (0)"
    Then I should see "No files match this decision filter"
    When I follow "Change configuration and create a new preview"
    Then the field "sourcefilters[edition]" matches value "2025"
    And the field "targetfilters[edition]" matches value "2026"

  Scenario: Delegated profile management has its own authorized entry
    Given the CourseFiles teacher can manage site profiles
    And I log in as "cbteacher"
    When I open CourseFiles administration "profiles"
    Then I should see "Migration acceptance"
    And I should see "Profiles" in the "[data-region='coursefiles-navigation']" "css_element"
    And I should not see "Settings" in the "[data-region='coursefiles-navigation']" "css_element"
    And I should not see "Migrations" in the "[data-region='coursefiles-navigation']" "css_element"
    And I should not see "Log" in the "[data-region='coursefiles-navigation']" "css_element"

  Scenario: Guided profile editing does not require a configuration document
    Given I log in as "admin"
    When I open CourseFiles administration "profiles"
    And I follow "Migration acceptance"
    Then "#id_configjson" "css_element" should not be visible
    When I set the field "Name" to "Guided profile"
    And I press "Save profile"
    Then I should see "Guided profile"
    And I should not see "Migration acceptance"

  Scenario: Migration exclusions survive searches and the execution step
    Given I log in as "admin"
    When I open CourseFiles administration "migration"
    And I follow "New migration"
    And I press "Continue to source and destination"
    And I set the field "sourcefilters[edition]" to "2025"
    And I set the field "targetfilters[edition]" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I press "Create preview"
    And I click on "[data-coursefiles-selectall]" "css_element"
    And I wait for the CourseFiles migration selection to save
    Then I should see "0 files included across all pages"
    When I set the field "Find a course, folder or file" to "lesson"
    And I wait for the CourseFiles migration selection to save
    And I click on "[data-coursefiles-review-search] button" "css_element"
    Then I should see "lesson.txt"
    And I should see "Excluded"
    When I click on "[data-coursefiles-selectall]" "css_element"
    And I wait for the CourseFiles migration selection to save
    Then I should see "2 files included across all pages"
    When I click on "input[name='included[]']" "css_element"
    And I wait for the CourseFiles migration selection to save
    And I click on "[data-coursefiles-review-search] button" "css_element"
    Then I should see "1 files included across all pages"
    When I click on "[data-coursefiles-selectall]" "css_element"
    And I wait for the CourseFiles migration selection to save
    And I follow "Continue to execution"
    Then I should see "Files are processed in the background without enabling site maintenance"
    And "input[name='confirmed']" "css_element" should not exist
    And I should not see "Find a course, folder or file"

  @_file_upload
  Scenario: Import a portable profile through the native upload control
    Given I log in as "admin"
    When I open CourseFiles administration "profiles"
    And I follow "Import profiles"
    And I upload "local/coursefiles/examples/institutional-profile.json" file to "File" filemanager
    And I press "Import profiles"
    And I capture CourseFiles administration "profile-import-result"
    Then I should see "Imported 1 new profiles"
    And I should see "UUID course families with optional annual editions"
    And I should see "Migration acceptance"

  Scenario: Material reuse logs are available as a native report
    Given I log in as "admin"
    When I navigate to "Reports > CourseFiles material reuse" in site administration
    When I click on ".coursefiles-filter-popover summary" "css_element"
    Then I should see "From date"
    And I should see "Through date"
    When I set the field "Action" to "profile_activated"
    And I click on ".coursefiles-log button[type='submit']" "css_element"
    Then I should see "Profile activated"
    And I capture CourseFiles administration "log"

  Scenario: Create and edit a blank guided profile with English plugin strings on a Spanish site
    Given the CourseFiles administrator uses Spanish
    And I log in as "admin"
    When I open CourseFiles administration "profiles"
    And I follow "New profile"
    Then the field "profilename" matches value ""
    And the field "coursepattern" matches value ""
    And the field "familytemplate" matches value ""
    And the field "displaytemplate" matches value ""
    And the CourseFiles profile inputs should align
    And I capture CourseFiles administration "blank-profile"
    When I set the field "profilename" to "Perfil nuevo"
    And I set the field "coursepattern" to "~^(?<group>.+)$~"
    And I set the field "familytemplate" to "${group}"
    And I press "Save profile"
    Then I should see "Perfil nuevo"
    When I follow "Perfil nuevo"
    And I set the field "profilename" to "Perfil editado"
    And I press "Save profile"
    Then I should see "Perfil editado"
    And I capture CourseFiles administration "spanish-profiles"

  Scenario: Edit a blocked migration without rewriting its original review
    Given I log in as "admin"
    When I open CourseFiles administration "migration"
    And I follow "New migration"
    And I press "Continue to source and destination"
    And the CourseFiles correspondence rows should fit narrow screens
    And I set the field "sourcefilters[edition]" to "2025"
    And I set the field "targetfilters[edition]" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I set the CourseFiles destination folder rule to "New destination"
    And I press "Create preview"
    Then I should see "Blocked"
    When I open CourseFiles administration "migration"
    And I click on "a[title='Edit']" "css_element"
    Then the field "sourcefilters[edition]" matches value "2025"
    And the field "targetfilters[edition]" matches value "2026"
    When I set the field "missingtarget" to "create"
    And I press "Create preview"
    Then I should see "Continue to execution"
    And CourseFiles should retain "2" migration plans

  Scenario: Compare and restore versions without creating another profile
    Given I log in as "admin"
    When I open CourseFiles administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "profilename" to "Updated acceptance"
    And I press "Save profile"
    And I follow "Updated acceptance"
    And I follow "Version history"
    Then I should see "Select two versions to compare"
    And I capture CourseFiles administration "version-history"
    And the CourseFiles history Back action should stay on the left
    When I compare the oldest CourseFiles profile version
    Then I should see "Updated acceptance" in the ".coursefiles-version-diff" "css_element"
    And I capture CourseFiles administration "version-compare"
    When I press "Restore this version"
    Then I should see "The selected version is now active"
    When I open CourseFiles administration "profiles"
    Then I should see "Migration acceptance" in the ".coursefiles-profiles table" "css_element"
    And I should not see "Updated acceptance" in the ".coursefiles-profiles table" "css_element"

  Scenario: Resolve a missing destination in place and keep Continue visible
    Given I log in as "admin"
    When I open CourseFiles administration "migration"
    And I follow "New migration"
    And I press "Continue to source and destination"
    And I set the field "sourcefilters[edition]" to "2025"
    And I set the field "targetfilters[edition]" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I set the CourseFiles destination folder rule to "Missing destination"
    And I press "Create preview"
    Then "[data-coursefiles-continue][aria-disabled='true']" "css_element" should exist
    And "[data-coursefiles-continue]" "css_element" should be visible
    When I set the field "mp-issue-0" to "Use: Target materials"
    And I wait for the CourseFiles migration selection to save
    Then "[data-coursefiles-continue][aria-disabled='false']" "css_element" should exist
    And I should see "lesson.txt"
    And I capture CourseFiles administration "resolved-migration"
    When I follow "Continue to execution"
    Then I should see "Files are processed in the background without enabling site maintenance"
    And "input[name='confirmed']" "css_element" should not exist
