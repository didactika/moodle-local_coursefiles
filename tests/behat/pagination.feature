@local @local_filebank @filebank_pagination @javascript
Feature: Bounded shared pagination and automatic whole-plan selections
  As a FileBank administrator
  I need to navigate records without changing selections on other pages

  Scenario: Lists share page controls and migration selection keeps visible nodes
    Given the following "local_filebank > pagination fixtures" exist:
      | name             |
      | Paged acceptance |
    And I log in as "admin"
    When I open FileBank administration "profiles"
    Then I should see "1–25 of 27"
    When I click on ".filebank-pagination a[title='Next']" "css_element"
    Then I should see "26–27 of 27"
    When I click on ".filebank-pagination a[title='Previous']" "css_element"
    And I set the field "Items per page" to "10"
    Then I should see "1–10 of 27"
    When I open FileBank administration "migration"
    Then I should see "1–25 of 26"
    And FileBank migration bulk actions should be "hidden"
    When I click on "input[name='plans[]']" "css_element"
    Then FileBank migration bulk actions should be "visible"
    When I click on ".filebank-pagination a[title='Next']" "css_element"
    Then I should see "26–26 of 26"
    When I click on "a[title='View']" "css_element"
    Then I should see "1–50 of 62"
    When I set the field "Items per page" to "10"
    And I wait for the FileBank migration selection to save
    Then I should see "1–10 of 62"
    When I remember the visible FileBank migration item
    And I click on "input[name='included[]']" "css_element"
    And I wait for the FileBank migration selection to save
    Then the visible FileBank migration item should be preserved
    And I should see "61 files included across all pages"
    When I click on "[data-filebank-selectall]" "css_element"
    And I wait for the FileBank migration selection to save
    Then I should see "62 files included across all pages"
    When I click on "[data-filebank-selectall]" "css_element"
    And I wait for the FileBank migration selection to save
    Then I should see "0 files included across all pages"
    And "[data-filebank-continue][aria-disabled='true']" "css_element" should exist
    When I click on ".filebank-pagination a[title='Next']" "css_element"
    And I wait for the FileBank migration selection to save
    Then I should see "11–20 of 62"
    And I should see "Excluded"
    And I capture FileBank administration "paged-migration"
