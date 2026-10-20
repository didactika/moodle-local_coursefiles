@local @local_filebank @filebank_migration @javascript
Feature: Decide what happens with the files in conflict before the migration is created
  As a FileBank administrator
  I need to say in the last step what happens with the files that are already different in the destination, so a migration
  never waits for a decision once it exists

  Background:
    Given the following "local_filebank > conflict fixtures" exist:
      | name                 |
      | Conflicts acceptance |
    And I log in as "admin"
    And I open FileBank administration "migration"
    And I set up a FileBank migration of "Published materials" into "Target materials" from "2025" to "2026"
    Then I should see "12 files already have a different file in the destination" in the "[data-region='conflict-choice']" "css_element"
    And I capture FileBank administration "migration-conflict-choice" at "[data-region='conflict-choice']"

  Scenario: Files in conflict are kept as they are and the migration is created without anything to decide
    When I press "Create migration"
    And I wait until "[data-region='migration-review']" "css_element" exists
    Then I should see "Left out" in the "[data-region='summary']" "css_element"
    And I should not see "Decisions" in the "[data-region='migration-review']" "css_element"
    And FileBank should retain "1" migration plans
    And I capture FileBank administration "conflicts-kept"
    When I click on "[data-region='files'] .fbm-filterbox summary" "css_element"
    And I click on "Left as they are" "button" in the "[data-region='files'] .fbm-filterpanel" "css_element"
    Then I wait until "[data-region='files'] .fbm-filterbox[data-active]" "css_element" exists

  Scenario: Files in conflict are overwritten when that is chosen and the migration runs
    When I click on "Overwrite it" "text" in the "[data-region='conflict-choice']" "css_element"
    And I wait until "[data-region='check-result'] .fbm-choice.is-on input[value='overwrite']" "css_element" exists
    And I press "Create migration"
    And I wait until "[data-region='migration-review']" "css_element" exists
    Then I should see "To overwrite" in the "[data-region='summary']" "css_element"
    And FileBank should retain "1" migration plans
    When I press "Start migration"
    And I capture FileBank administration "start-confirm"
    And I click on "Start" "button" in the "dialog" "css_element"
    And I run all adhoc tasks
    And I wait "3" seconds
    And I reload the page
    And I wait until "[data-region='migration-review']" "css_element" exists
    Then I should see "Completed" in the "[data-region='migration-review']" "css_element"
    And I capture FileBank administration "run-completed"
