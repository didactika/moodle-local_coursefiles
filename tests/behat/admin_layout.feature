@local @local_multipicker @multipicker_admin @javascript
Feature: Clear administration tabs and immutable migration previews
  As a MultiPicker administrator
  I need separate configuration, review and execution screens

  Background:
    Given the following "local_multipicker > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Native settings save without leaving the administration tabs
    Given I log in as "admin"
    When I open MultiPicker administration "settings"
    Then I should see "Profiles" in the "[data-region='multipicker-navigation']" "css_element"
    And I should see "Migrations" in the "[data-region='multipicker-navigation']" "css_element"
    And I should see "Log" in the "[data-region='multipicker-navigation']" "css_element"
    When I press "Save changes"
    Then I should see "Changes saved"
    And I capture MultiPicker administration "settings"

  Scenario: Previewing edited profile rules keeps the saved profile unchanged
    Given I log in as "admin"
    When I open MultiPicker administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "Name" to "Unsaved profile name"
    And I press "Preview rules"
    Then I should see "Nothing has been saved or activated"
    And I should see "Unsaved profile name"
    And I capture MultiPicker administration "profile-editor"
    When I press "Cancel"
    Then I should see "Migration acceptance"
    And I should not see "Unsaved profile name"
    And I capture MultiPicker administration "profiles"

  Scenario: A stored migration preview groups files and exposes decision filters
    Given I log in as "admin"
    When I open MultiPicker administration "migration"
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
    Then I should see "Execution becomes available when site maintenance mode is enabled"
    When I follow "Back to review"
    And I capture MultiPicker administration "migration-review"
    When I follow "Already available (0)"
    Then I should see "No files match this decision filter"
    When I follow "Change configuration and create a new preview"
    Then the field "sourcefilters[edition]" matches value "2025"
    And the field "targetfilters[edition]" matches value "2026"

  Scenario: Delegated profile management has its own authorized entry
    Given the MultiPicker teacher can manage site profiles
    And I log in as "cbteacher"
    When I open MultiPicker administration "profiles"
    Then I should see "Migration acceptance"
    And I should see "Profiles" in the "[data-region='multipicker-navigation']" "css_element"
    And I should not see "Settings" in the "[data-region='multipicker-navigation']" "css_element"
    And I should not see "Migrations" in the "[data-region='multipicker-navigation']" "css_element"
    And I should not see "Log" in the "[data-region='multipicker-navigation']" "css_element"

  Scenario: Guided profile editing does not require a configuration document
    Given I log in as "admin"
    When I open MultiPicker administration "profiles"
    And I follow "Migration acceptance"
    Then "#id_configjson" "css_element" should not be visible
    When I set the field "Name" to "Guided profile"
    And I press "Validate and activate revision"
    Then I should see "Guided profile"
    And I should not see "Migration acceptance"

  Scenario: Migration exclusions survive searches and the execution step
    Given I log in as "admin"
    When I open MultiPicker administration "migration"
    And I follow "New migration"
    And I press "Continue to source and destination"
    And I set the field "sourcefilters[edition]" to "2025"
    And I set the field "targetfilters[edition]" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I press "Create preview"
    And I press "Exclude every file"
    Then I should see "0 files included across all pages"
    When I set the field "Find a course, folder or file" to "lesson"
    And I click on ".multipicker-migration form[method='get'] button" "css_element"
    Then I should see "lesson.txt"
    And I should see "Excluded"
    When I press "Include every file"
    Then I should see "2 files included across all pages"
    When I click on "input[name='included[]']" "css_element"
    And I click on ".multipicker-migration form[method='get'] button" "css_element"
    Then I should see "1 files included across all pages"
    When I press "Include every file"
    And I follow "Continue to execution"
    Then I should see "I have reviewed the complete preview"
    And I should not see "Find a course, folder or file"

  @_file_upload
  Scenario: Import a portable profile through the native upload control
    Given I log in as "admin"
    When I open MultiPicker administration "profiles"
    And I follow "Import profiles"
    And I upload "local/multipicker/examples/institutional-profile.json" file to "File" filemanager
    And I press "Import profiles"
    And I capture MultiPicker administration "profile-import-result"
    Then I should see "Imported 1 new profiles"
    And I should see "UUID course families with optional annual editions"
    And I should see "Migration acceptance"

  Scenario: Material reuse logs are available as a native report
    Given I log in as "admin"
    When I navigate to "Reports > MultiPicker material reuse" in site administration
    When I click on ".multipicker-filter-popover summary" "css_element"
    Then I should see "From date"
    And I should see "Through date"
    When I set the field "Action" to "profile_activated"
    And I click on ".multipicker-log button[type='submit']" "css_element"
    Then I should see "Profile activated"
    And I capture MultiPicker administration "log"
