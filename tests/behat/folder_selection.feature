@local @local_coursebridge @javascript
Feature: Import mixed materials inside the native folder picker
  As a destination course teacher
  I need a visual batch selection that preserves native saving and cancellation

  Background:
    Given the following "local_coursebridge > folder fixtures" exist:
      | name              |
      | Folder acceptance |
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I press "Import materials"

  Scenario: Import two folders and a resource and save with the native content form
    When I select CourseBridge material "Published materials" from "CB2024"
    And I select CourseBridge material "Published materials" from "CB2025"
    And I select CourseBridge material "resource.txt" from "CB2025"
    And I press CourseBridge action "Review selection"
    Then the CourseBridge review should include "notes2024.txt"
    And the CourseBridge review should include "notes2025.txt"
    And the CourseBridge review should include "Original teacher"
    When I press CourseBridge action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "existing.txt"
    And I should see "lesson.txt"
    And I should see "resource.txt"
    And I should see "notes2024.txt"
    And I should see "notes2025.txt"

  Scenario: Cancel retains the previously saved folder
    When I select CourseBridge material "Published materials" from "CB2025"
    And I press CourseBridge action "Review selection"
    And I press CourseBridge action "Import reviewed materials"
    And I press "Cancel"
    Then I should see "existing.txt"
    And I should not see "lesson.txt"

  Scenario: A content-only teacher imports without opening activity settings
    Given the CourseBridge teacher can only manage folder content
    When I select CourseBridge material "Published materials" from "CB2025"
    And I press CourseBridge action "Review selection"
    And I press CourseBridge action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "lesson.txt"
    And I should see "notes2025.txt"
