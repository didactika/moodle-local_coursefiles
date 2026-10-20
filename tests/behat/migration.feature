@local @local_filebank @filebank_migration @javascript
Feature: Migrations that never get in the way
  As a FileBank administrator
  I need to start, review and settle a migration without errors for what does not match and without new migrations for every change

  Background:
    Given the following "local_filebank > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Start a migration step by step, check it first and review it
    Given I log in as "admin"
    When I open FileBank administration "migration"
    Then I should see "No migrations yet"
    And I capture FileBank administration "migration-empty"
    When I click on "[data-action='new-migration']" "css_element"
    Then I should see "Set it up step by step" in the "[data-region='migration-wizard']" "css_element"
    And I capture FileBank administration "migration-wizard-profile"
    When I click on "[data-action='next-step']" "css_element"
    And I set the field "fbm-source-edition" to "2025"
    And I set the field "fbm-target-edition" to "2026"
    And I capture FileBank administration "migration-wizard-courses"
    And I click on "[data-action='next-step']" "css_element"
    Then I should see "No correspondence yet" in the "[data-region='migration-wizard']" "css_element"
    And I capture FileBank administration "migration-wizard-folders-empty"
    When I click on "[data-action='add-correspondence']" "css_element"
    And I set the field "fbm-c0-source-value" to "Published materials"
    And I set the field "fbm-c0-target-value" to "Target materials"
    And I capture FileBank administration "migration-wizard-folders"
    And I click on "[data-action='next-step']" "css_element"
    And I capture FileBank administration "migration-wizard-sections"
    And I click on "[data-action='next-step']" "css_element"
    And I capture FileBank administration "migration-wizard-policies"
    And I press "Check what it would do"
    And I wait until "[data-region='check-result']" "css_element" exists
    Then I should see "To copy" in the "[data-region='check-result']" "css_element"
    And I capture FileBank administration "migration-check"
    When I press "Create migration"
    Then I should see "Migration #" in the "[data-region='migration-review']" "css_element"
    And I capture FileBank administration "migration-review"
    When I click on "[data-tab='files']" "css_element"
    Then I should see "lesson.txt" in the "[data-region='files']" "css_element"
    And I capture FileBank administration "migration-files" at "[data-region='files']"
    When I click on "[data-tab='folders']" "css_element"
    And I capture FileBank administration "migration-folders" at "[data-region='folders']"

  Scenario: Nothing is chosen for the person, a folder that is not there is no error and changes create no other migration
    Given I log in as "admin"
    When I open FileBank administration "migration"
    And I set up a FileBank migration of "A folder that is not there" into "Target materials" from "2025" to "2026"
    Then I should see "Nothing to migrate"
    And the "Create migration" "button" should be disabled
    And I capture FileBank administration "migration-nothing"
    When I click on "[data-step='folders']" "css_element"
    And I set the field "fbm-c0-source-value" to "Published materials"
    And I click on "[data-step='check']" "css_element"
    And I wait until "[data-region='check-result']" "css_element" exists
    And I press "Create migration"
    Then I should see "Migration #" in the "[data-region='migration-review']" "css_element"
    And FileBank should retain "1" migration plans
    When I click on "[data-action='edit-parameters']" "css_element"
    And I click on "[data-step='folders']" "css_element"
    And I set the field "fbm-c0-target-value" to "Missing destination"
    And I click on "[data-step='policies']" "css_element"
    And I press "Leave it out"
    And I capture FileBank administration "migration-edit"
    And I click on "[data-step='check']" "css_element"
    And I wait until "[data-region='empty-state']" "css_element" exists
    And I press "Save and recalculate"
    Then I should see "Nothing to write" in the "[data-region='migration-review']" "css_element"
    And FileBank should retain "1" migration plans
    And I capture FileBank administration "migration-edited"
