@local @local_filebank @filebank_migration @javascript
Feature: Settle what needs a decision all at once
  As a FileBank administrator
  I need to settle hundreds of files in conflict with one choice, inside the same migration, and never be blocked by them

  Background:
    Given the following "local_filebank > conflict fixtures" exist:
      | name                 |
      | Conflicts acceptance |
    And I log in as "admin"
    And I open FileBank administration "migration"
    And I set up a FileBank migration of "Published materials" into "Target materials" from "2025" to "2026"
    And I press "Create migration"
    And I wait until "[data-region='migration-review']" "css_element" exists

  Scenario: Files in conflict never block and are left as they are with one click
    Then I should see "A different file is already there" in the "[data-region='decisions']" "css_element"
    And I should see "To decide" in the "[data-region='summary']" "css_element"
    And I capture FileBank administration "decisions-open"
    When I press "Review one by one"
    And I wait until "[data-region='decision-list']" "css_element" exists
    And I capture FileBank administration "decisions-list" at "[data-region='decisions']"
    When I press "Apply to all 12: Keep the destination as it is"
    And I click on "Apply" "button" in the "dialog" "css_element"
    Then I should see "Everything is settled" in the "[data-region='decisions']" "css_element"
    And FileBank should retain "1" migration plans
    And I capture FileBank administration "decisions-settled"
    When I click on "[data-tab='files']" "css_element"
    And I click on "Left as they are" "button"
    Then I should see "Left as they are"

  Scenario: Files in conflict are overwritten together and the migration runs
    When I press "Apply to all 12: Overwrite the file in the destination"
    And I click on "Apply" "button" in the "dialog" "css_element"
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
