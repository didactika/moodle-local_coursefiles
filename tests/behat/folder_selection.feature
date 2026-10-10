@local @local_coursefiles @coursefiles_folder @javascript
Feature: Import mixed materials inside the native folder picker
  As a destination course teacher
  I need a visual batch selection that preserves native saving and cancellation

  Background:
    Given the following "local_coursefiles > folder fixtures" exist:
      | name              |
      | Folder acceptance |
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native CourseFiles

  Scenario: Import two folders and a resource and save with the native content form
    Then the CourseFiles module filter should be "folder"
    When I set CourseFiles filter "module" to ""
    And I capture the CourseFiles browser
    And I select CourseFiles material "Published materials" from "CB2024"
    And I select CourseFiles material "Published materials" from "CB2025"
    And I select CourseFiles material "resource.txt" from "CB2025"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2024.txt"
    And the CourseFiles review should include "notes2025.txt"
    And the CourseFiles review should include "Original teacher"
    And I capture the CourseFiles browser
    When I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "existing.txt"
    And I should see "lesson.txt"
    And I should see "resource.txt"
    And I should see "notes2024.txt"
    And I should see "notes2025.txt"

  Scenario: Cancel retains the previously saved folder
    When I select CourseFiles material "Published materials" from "CB2025"
    And I press CourseFiles action "Review selection"
    And I press CourseFiles action "Import materials"
    And I press "Cancel"
    Then I should see "existing.txt"
    And I should not see "lesson.txt"

  Scenario: A content-only teacher imports without opening activity settings
    Given the CourseFiles teacher can only manage folder content
    When I select CourseFiles material "Published materials" from "CB2025"
    And I press CourseFiles action "Review selection"
    And I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "lesson.txt"
    And I should see "notes2025.txt"

  Scenario: Remove a selected folder after filtering its card out of the page
    When I select CourseFiles material "Published materials" from "CB2024"
    And I select CourseFiles material "Published materials" from "CB2025"
    And I set CourseFiles filter "q" to "resource"
    And I remove CourseFiles reviewed file "notes2024.txt"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2025.txt"
    And the CourseFiles browser should not include "notes2024.txt"
    And I capture the CourseFiles browser
    When I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "notes2025.txt"
    And I should not see "notes2024.txt"

  Scenario: Review and return retain checks and functional list layout
    When I select CourseFiles material "Published materials" from "CB2025"
    And I switch CourseFiles layout to "list"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2025.txt"
    When I return to CourseFiles selection
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2025.txt"

  Scenario: Public assignment handouts are reusable through the normal folder picker
    When I set CourseFiles filter "module" to "assign"
    Then I should see "1" CourseFiles file cards named "handout.txt"
    And the CourseFiles browser should not include "student-private.txt"
    When I select CourseFiles material "handout.txt" from "CB2025"
    And I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "handout.txt"
    When I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "handout.txt"

  Scenario: Recursive selection can exclude a child and retain the enclosing folder paths
    When I select CourseFiles material "Published materials" from "CB2025"
    Then CourseFiles should have "2" selected files
    When I open CourseFiles folder "Published materials" from "CB2025"
    And I deselect CourseFiles file "lesson.txt"
    Then CourseFiles should have "1" selected files
    When I press CourseFiles action "Review selection"
    Then the CourseFiles review should include "notes2025.txt"
    And the CourseFiles browser should not include "lesson.txt"
    When I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "notes2025.txt"
    And I should not see "lesson.txt"

  Scenario: Rename a reviewed file before importing into the native folder
    When I select CourseFiles material "Published materials" from "CB2025"
    And I press CourseFiles action "Review selection"
    And I rename CourseFiles reviewed file "lesson.txt" to "renamed-lesson.txt"
    Then the CourseFiles review should include "renamed-lesson.txt"
    When I press CourseFiles action "Import materials"
    And I press "Save changes"
    Then I should see "renamed-lesson.txt"
    And "//a[normalize-space()='lesson.txt']" "xpath_element" should not exist
