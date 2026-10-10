@local @local_coursefiles @coursefiles_editor @javascript
Feature: Reuse several public files from an existing activity editor
  As a course teacher
  I need the native link picker to insert the entire reviewed selection

  Background:
    Given the following "local_coursefiles > folder fixtures" exist:
      | name              |
      | Editor acceptance |
    And I am on the "Target URL" "url activity" page logged in as "cbteacher"
    And I navigate to "Settings" in current page administration
    And I open CourseFiles from the activity editor

  Scenario: Insert several links and save an existing URL activity
    Then the CourseFiles module filter should be "url"
    When I set CourseFiles filter "module" to ""
    And I set CourseFiles filter "q" to "lesson"
    And I select CourseFiles material "lesson.txt" from "CB2024"
    And I set CourseFiles filter "q" to "resource"
    And I select CourseFiles material "resource.txt" from "CB2025"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "resource.txt"
    When I press CourseFiles action "Import materials"
    Then the activity editor should contain CourseFiles links "lesson.txt,resource.txt"
    When I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the activity editor should contain CourseFiles links "lesson.txt,resource.txt"
