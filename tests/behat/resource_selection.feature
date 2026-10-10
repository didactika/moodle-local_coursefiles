@local @local_coursefiles @javascript
Feature: Import supporting materials without changing the native resource main file
  As a destination course teacher
  I need the existing File main file and ordinary Save or Cancel controls

  Background:
    Given the following "local_coursefiles > folder fixtures" exist:
      | name                |
      | Resource acceptance |
    And I am on the "Target resource" "resource activity" page logged in as "cbteacher"
    And I navigate to "Settings" in current page administration
    And I open the native CourseFiles
    Then the CourseFiles module filter should be "resource"
    When I set CourseFiles filter "module" to ""

  Scenario: Save several supporting materials and reopen native settings
    When I select CourseFiles material "Published materials" from "CB2025"
    And I select CourseFiles material "resource.txt" from "CB2025"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2025.txt"
    When I press CourseFiles action "Import materials"
    And I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then I should see "4" elements in "Select files" filemanager

  Scenario: Cancel keeps the saved resource unchanged
    When I select CourseFiles material "Published materials" from "CB2025"
    And I press CourseFiles action "Review selection"
    And I press CourseFiles action "Import materials"
    And I press "Cancel"
    And I am on the "Target resource" "resource activity" page
    And I navigate to "Settings" in current page administration
    Then I should see "1" elements in "Select files" filemanager
