@local @local_coursebridge @coursebridge_admin @javascript
Feature: Clear administration tabs and immutable migration previews
  As a CourseBridge administrator
  I need separate configuration, review and execution screens

  Background:
    Given the following "local_coursebridge > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Native settings save without leaving the administration tabs
    Given I log in as "admin"
    When I open CourseBridge administration "settings"
    Then I should see "Profiles" in the "[data-region='coursebridge-navigation']" "css_element"
    And I should see "Migrations" in the "[data-region='coursebridge-navigation']" "css_element"
    And I should see "Log" in the "[data-region='coursebridge-navigation']" "css_element"
    When I press "Save changes"
    Then I should see "Changes saved"
    And I capture CourseBridge administration "settings"

  Scenario: Previewing edited profile rules keeps the saved profile unchanged
    Given I log in as "admin"
    When I open CourseBridge administration "profiles"
    And I follow "Migration acceptance"
    And I set the field "Name" to "Unsaved profile name"
    And I press "Preview rules"
    Then I should see "Nothing has been saved or activated"
    And I should see "Unsaved profile name"
    And I capture CourseBridge administration "profile-editor"
    When I press "Cancel"
    Then I should see "Migration acceptance"
    And I should not see "Unsaved profile name"
    And I capture CourseBridge administration "profiles"

  Scenario: A stored migration preview groups files and exposes decision filters
    Given I log in as "admin"
    When I open CourseBridge administration "migration"
    Then I should see "Migration rules"
    And I should see "Published materials"
    When I set the field "Source edition" to "2025"
    And I set the field "Target edition" to "2026"
    And I set the field "layoutconfirmed" to "1"
    And I press "Create preview"
    Then I should see "CB2025"
    And I should see "CB2026"
    And I should see "lesson.txt"
    And I should see "Original teacher"
    And I should see "Execution becomes available when site maintenance mode is enabled"
    And I capture CourseBridge administration "migration-review"
    When I follow "Already available (0)"
    Then I should see "No files match this decision filter"
    When I follow "Change configuration and create a new preview"
    Then the field "Source edition" matches value "2025"
    And the field "Target edition" matches value "2026"

  Scenario: Delegated profile management has its own authorized entry
    Given the CourseBridge teacher can manage site profiles
    And I log in as "cbteacher"
    When I open CourseBridge administration "profiles"
    Then I should see "Migration acceptance"
    And I should see "Profiles" in the "[data-region='coursebridge-navigation']" "css_element"
    And I should not see "Settings" in the "[data-region='coursebridge-navigation']" "css_element"
    And I should not see "Migrations" in the "[data-region='coursebridge-navigation']" "css_element"
    And I should not see "Log" in the "[data-region='coursebridge-navigation']" "css_element"
