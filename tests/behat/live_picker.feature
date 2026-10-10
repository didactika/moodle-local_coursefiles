@local @local_multipicker @javascript
Feature: Stable native selection with the configured repository name
  As a teacher
  I need continuous filtering and a recognizable material library

  Background:
    Given the following "local_multipicker > folder fixtures" exist:
      | name           |
      | Live selection |
    And the native MultiPicker repository is named "Course library"
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native repository "Course library"

  Scenario: Configured name and whole-result selection use the native picker
    Then the MultiPicker heading should be "Course library"
    And MultiPicker cards and filters should keep a compact aligned layout
    When I press MultiPicker action "Select all"
    Then MultiPicker should have "4" selected files
    When I press MultiPicker action "Clear selection"
    Then MultiPicker should have "0" selected files

  Scenario: Typing remains active and the modal does not resize while results reload
    When I filter MultiPicker while its results are loading
    Then I should see "1" MultiPicker file cards named "notes2025.txt"
    And I capture the MultiPicker browser

  Scenario: Folder selection preserves cards and the current results scroll
    When I select a MultiPicker card without rebuilding its results
    Then MultiPicker should have "2" selected files

  Scenario: Review rows select files and retain native import approval
    When I press MultiPicker action "Select all"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should use compact selectable rows
    And I capture the MultiPicker browser
