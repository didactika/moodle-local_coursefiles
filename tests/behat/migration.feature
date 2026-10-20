@local @local_filebank @filebank_migration @javascript
Feature: Migrations that never get in the way
  As a FileBank administrator
  I need to start, review and settle a migration without errors for what does not match and without new migrations for every change

  Background:
    Given the following "local_filebank > migration fixtures" exist:
      | name                 |
      | Migration acceptance |

  Scenario: Start a migration from the window, check it first and review it
    Given I log in as "admin"
    When I open FileBank administration "migration"
    Then I should see "No migrations yet"
    And I capture FileBank administration "migration-empty"
    When I click on "[data-action='new-migration']" "css_element"
    And I set the field "fbm-source-edition" to "2025"
    And I set the field "fbm-target-edition" to "2026"
    And I set the field "fbm-c0-source-method" to "Exactly"
    And I set the field "fbm-c0-source-value" to "Published materials"
    And I set the field "fbm-c0-target-method" to "Exactly"
    And I set the field "fbm-c0-target-value" to "Target materials"
    And I capture FileBank administration "migration-wizard"
    And I press "Check what it would do"
    And I wait "4" seconds
    And I capture FileBank administration "migration-check-debug"
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

  Scenario: A source folder that does not exist is not an error and changing the parameters creates no other migration
    Given I log in as "admin"
    When I open FileBank administration "migration"
    And I click on "[data-action='new-migration']" "css_element"
    And I set the field "fbm-source-edition" to "2025"
    And I set the field "fbm-target-edition" to "2026"
    And I set the field "fbm-c0-source-value" to "A folder that is not there"
    And I set the field "fbm-c0-source-method" to "Exactly"
    And I press "Check what it would do"
    And I wait until "[data-region='empty-state']" "css_element" exists
    Then I should see "Nothing to migrate"
    And the "Create migration" "button" should be disabled
    And I capture FileBank administration "migration-nothing"
    When I press "Change the parameters"
    And I set the field "fbm-c0-source-value" to "Published materials"
    And I set the field "fbm-c0-target-method" to "Exactly"
    And I set the field "fbm-c0-target-value" to "Target materials"
    And I press "Check what it would do"
    And I press "Create migration"
    Then I should see "Migration #" in the "[data-region='migration-review']" "css_element"
    And FileBank should retain "1" migration plans
    When I click on "[data-action='edit-parameters']" "css_element"
    And I set the field "fbm-c0-target-value" to "Missing destination"
    And I press "Leave it out"
    And I press "Save and recalculate"
    Then I should see "Nothing to write" in the "[data-region='migration-review']" "css_element"
    And FileBank should retain "1" migration plans
    And I capture FileBank administration "migration-edited"
