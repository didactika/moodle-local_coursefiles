@local @local_filebank @filebank_pagination @javascript
Feature: Bounded shared pagination and whole-migration selections
  As a FileBank administrator
  I need to navigate records without changing selections on other pages

  Scenario: Lists share page controls and the selection of files covers every page
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
    Then I should see "1 - 10 of 26"
    When I click on "button[title='Next page']" "css_element"
    Then I should see "11 - 20 of 26"
    And I capture FileBank administration "paged-migrations"
    When I click on "tr[data-migration] .fbm-link" "css_element"
    And I wait until "[data-region='migration-review']" "css_element" exists
    And I click on "[data-tab='files']" "css_element"
    Then I should see "1 - 25 of 62" in the "[data-region='files']" "css_element"
    When I click on "Exclude all" "button"
    Then I should see "Nothing to write." in the "[data-region='migration-review']" "css_element"
    And I capture FileBank administration "paged-files" at "[data-region='files']"
