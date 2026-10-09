@local @local_multipicker @multipicker_editor @javascript
Feature: Reuse several public files from an existing activity editor
  As a course teacher
  I need the native link picker to insert the entire reviewed selection

  Background:
    Given the following "local_multipicker > folder fixtures" exist:
      | name              |
      | Editor acceptance |
    And I am on the "Target URL" "url activity" page logged in as "cbteacher"
    And I navigate to "Settings" in current page administration
    And I open MultiPicker from the activity editor

  Scenario: Insert several links and save an existing URL activity
    Then the MultiPicker module filter should be "url"
    When I set MultiPicker filter "module" to ""
    And I set MultiPicker filter "q" to "lesson"
    And I select MultiPicker material "lesson.txt" from "CB2024"
    And I set MultiPicker filter "q" to "resource"
    And I select MultiPicker material "resource.txt" from "CB2025"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "resource.txt"
    When I press MultiPicker action "Import reviewed materials"
    Then the activity editor should contain MultiPicker links "lesson.txt,resource.txt"
    When I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the activity editor should contain MultiPicker links "lesson.txt,resource.txt"
