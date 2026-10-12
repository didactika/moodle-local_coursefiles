@local @local_filebank @filebank_editor @javascript
Feature: Reuse several public files from an existing activity editor
  As a course teacher
  I need the native link picker to insert the entire reviewed selection

  Background:
    Given the following "local_filebank > folder fixtures" exist:
      | name              |
      | Editor acceptance |
    And I am on the "Target URL" "url activity" page logged in as "cbteacher"
    And I navigate to "Settings" in current page administration
    And I open FileBank from the activity editor

  Scenario: Insert several links and save an existing URL activity
    Then the FileBank module filter should be "url"
    When I set FileBank filter "module" to ""
    And I set FileBank filter "q" to "lesson"
    And I select FileBank material "lesson.txt" from "CB2024"
    And I set FileBank filter "q" to "resource"
    And I select FileBank material "resource.txt" from "CB2025"
    And I press FileBank action "Review selection"
    Then the FileBank review should include "resource.txt"
    When I press FileBank action "Import materials"
    Then the activity editor should contain FileBank links "lesson.txt,resource.txt"
    When I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the activity editor should contain FileBank links "lesson.txt,resource.txt"
