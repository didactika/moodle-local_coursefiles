@local @local_filebank @filebank_admin @javascript
Feature: Clear administration tabs and immutable migration previews
  As a FileBank administrator
  I need separate configuration, review and execution screens

  Background:
    Given the following "local_filebank > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Profile bulk icons are hidden until profiles are selected
    Given I log in as "admin"
    When I open FileBank administration "profiles"
    Then FileBank profile bulk actions should be "hidden"
    When I click on "input[name='profiles[]']" "css_element"
    Then FileBank profile bulk actions should be "visible"
    And I capture FileBank administration "bulk-selected"
    When I click on "input[name='profiles[]']" "css_element"
    Then FileBank profile bulk actions should be "hidden"
    When I click on "[data-filebank-selectall]" "css_element"
    And I remember the selected FileBank profile rows
    And I press "Disable selected"
    Then I should see "Disabled" in the ".filebank-profiles table" "css_element"
    And FileBank profile bulk actions should be "visible"
    And FileBank should retain its selected profile rows
    And "button[value='disable']" "css_element" should not be visible
    When I press "Enable selected"
    Then I should see "Enabled" in the ".filebank-profiles table" "css_element"
    And I capture FileBank administration "bulk-icons"

  Scenario: Native settings save without leaving the administration tabs
    Given I log in as "admin"
    When I open FileBank administration "category"
    Then I should see "Profiles" in the "[data-region='filebank-navigation']" "css_element"
    And I should see "Migrations" in the "[data-region='filebank-navigation']" "css_element"
    And I should see "Log" in the "[data-region='filebank-navigation']" "css_element"
    When I press "Save changes"
    Then I should see "Changes saved"
    And I capture FileBank administration "settings"

  Scenario: Previewing edited profile rules keeps the saved profile unchanged
    Given I log in as "admin"
    When I open FileBank administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "Name" to "Unsaved profile name"
    And I press "Preview rules"
    Then I should see "Nothing has been saved or activated"
    And I should not see "Rules in use"
    And I should see "Unsaved profile name"
    And I capture FileBank administration "profile-editor"
    When I press "Cancel"
    Then I should see "Migration acceptance"
    And I should not see "Unsaved profile name"
    And I capture FileBank administration "profiles"

  Scenario: Delegated profile management has its own authorized entry
    Given the FileBank teacher can manage site profiles
    And I log in as "cbteacher"
    When I open FileBank administration "profiles"
    Then I should see "Migration acceptance"
    And I should see "Profiles" in the "[data-region='filebank-navigation']" "css_element"
    And I should not see "Settings" in the "[data-region='filebank-navigation']" "css_element"
    And I should not see "Migrations" in the "[data-region='filebank-navigation']" "css_element"
    And I should not see "Log" in the "[data-region='filebank-navigation']" "css_element"

  Scenario: Guided profile editing does not require a configuration document
    Given I log in as "admin"
    When I open FileBank administration "profiles"
    And I follow "Migration acceptance"
    Then "#id_configjson" "css_element" should not be visible
    When I set the field "Name" to "Guided profile"
    And I press "Save profile"
    Then I should see "Guided profile"
    And I should not see "Migration acceptance"

  @_file_upload
  Scenario: Import a portable profile through the native upload control
    Given I log in as "admin"
    When I open FileBank administration "profiles"
    And I follow "Import profiles"
    And I upload a generated FileBank profile to "File" filemanager
    And I press "Import profiles"
    And I capture FileBank administration "profile-import-result"
    Then I should see "Imported 1 new profiles"
    And I should see "Test course collection"
    And I should see "Migration acceptance"

  Scenario: Material reuse logs are available as a native report
    Given I log in as "admin"
    When I navigate to "Reports > FileBank material reuse" in site administration
    And I click on ".filebank-log .fbm-filterbox summary" "css_element"
    Then I should see "From date"
    And I should see "Through date"
    And I set the field "Action" to "profile_activated"
    And I wait until "[data-region='active-filters']" "css_element" exists
    Then I should see "Profile activated"
    And I capture FileBank administration "log"

  Scenario: Create and edit a blank guided profile with English plugin strings on a Spanish site
    Given the FileBank administrator uses Spanish
    And I log in as "admin"
    When I open FileBank administration "profiles"
    And I follow "New profile"
    Then the field "profilename" matches value ""
    And the field "coursepattern" matches value ""
    And the field "familytemplate" matches value ""
    And the field "displaytemplate" matches value ""
    And the FileBank profile inputs should align
    And I capture FileBank administration "blank-profile"
    When I set the field "profilename" to "Perfil nuevo"
    And I set the field "coursepattern" to "~^(?<group>.+)$~"
    And I set the field "familytemplate" to "${group}"
    And I press "Save profile"
    Then I should see "Perfil nuevo"
    When I follow "Perfil nuevo"
    And I set the field "profilename" to "Perfil editado"
    And I press "Save profile"
    Then I should see "Perfil editado"
    And I capture FileBank administration "spanish-profiles"

  Scenario: Compare and restore versions without creating another profile
    Given I log in as "admin"
    When I open FileBank administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "profilename" to "Updated acceptance"
    And I press "Save profile"
    And I follow "Updated acceptance"
    And I follow "Version history"
    Then I should see "Select two versions to compare"
    And I capture FileBank administration "version-history"
    And the FileBank history Back action should stay on the left
    When I compare the oldest FileBank profile version
    Then I should see "Updated acceptance" in the ".filebank-version-diff" "css_element"
    And I capture FileBank administration "version-compare"
    When I press "Restore this version"
    Then I should see "The selected version is now active"
    When I open FileBank administration "profiles"
    Then I should see "Migration acceptance" in the ".filebank-profiles table" "css_element"
    And I should not see "Updated acceptance" in the ".filebank-profiles table" "css_element"
