@local @local_filebank @filebank_folder @javascript
Feature: Import mixed materials inside the native folder picker
  As a destination course teacher
  I need a visual batch selection that preserves native saving and cancellation

  Background:
    Given the following "local_filebank > folder fixtures" exist:
      | name              |
      | Folder acceptance |
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native FileBank

  Scenario: Import two folders and a resource and save with the native content form
    Then the FileBank module filter should be "folder"
    When I set FileBank filter "module" to ""
    And I capture the FileBank browser
    And I select FileBank material "Published materials" from "CB2024"
    And I select FileBank material "Published materials" from "CB2025"
    And I select FileBank material "resource.txt" from "CB2025"
    And I press FileBank action "Review selection"
    Then the FileBank review should include "notes2024.txt"
    And the FileBank review should include "notes2025.txt"
    And the FileBank review should include "Original teacher"
    And I capture the FileBank browser
    When I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "existing.txt"
    And I should see "lesson.txt"
    And I should see "resource.txt"
    And I should see "notes2024.txt"
    And I should see "notes2025.txt"

  Scenario: Cancel retains the previously saved folder
    When I select FileBank material "Published materials" from "CB2025"
    And I press FileBank action "Review selection"
    And I press FileBank action "Import materials"
    And I press "Cancel"
    Then I should see "existing.txt"
    And I should not see "lesson.txt"

  Scenario: A content-only teacher imports without opening activity settings
    Given the FileBank teacher can only manage folder content
    When I select FileBank material "Published materials" from "CB2025"
    And I press FileBank action "Review selection"
    And I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "lesson.txt"
    And I should see "notes2025.txt"

  Scenario: Remove a selected folder after filtering its card out of the page
    When I select FileBank material "Published materials" from "CB2024"
    And I select FileBank material "Published materials" from "CB2025"
    And I set FileBank filter "q" to "resource"
    And I remove FileBank reviewed file "notes2024.txt"
    And I press FileBank action "Review selection"
    Then the FileBank review should include "notes2025.txt"
    And the FileBank browser should not include "notes2024.txt"
    And I capture the FileBank browser
    When I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "notes2025.txt"
    And I should not see "notes2024.txt"

  Scenario: Review and return retain checks and functional list layout
    When I select FileBank material "Published materials" from "CB2025"
    And I switch FileBank layout to "list"
    And I press FileBank action "Review selection"
    Then the FileBank review should include "notes2025.txt"
    When I return to FileBank selection
    And I press FileBank action "Review selection"
    Then the FileBank review should include "notes2025.txt"

  Scenario: Public assignment handouts are reusable through the normal folder picker
    When I set FileBank filter "module" to "assign"
    Then I should see "1" FileBank file cards named "handout.txt"
    And the FileBank browser should not include "student-private.txt"
    When I select FileBank material "handout.txt" from "CB2025"
    And I press FileBank action "Review selection"
    Then the FileBank review should include "handout.txt"
    When I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "handout.txt"

  Scenario: Recursive selection can exclude a child and retain the enclosing folder paths
    When I select FileBank material "Published materials" from "CB2025"
    Then FileBank should have "2" selected files
    When I open FileBank folder "Published materials" from "CB2025"
    And I deselect FileBank file "lesson.txt"
    Then FileBank should have "1" selected files
    When I press FileBank action "Review selection"
    Then the FileBank review should include "notes2025.txt"
    And the FileBank browser should not include "lesson.txt"
    When I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "notes2025.txt"
    And I should not see "lesson.txt"

  Scenario: Rename a reviewed file before importing into the native folder
    When I select FileBank material "Published materials" from "CB2025"
    And I press FileBank action "Review selection"
    And I rename FileBank reviewed file "lesson.txt" to "renamed-lesson.txt"
    Then the FileBank review should include "renamed-lesson.txt"
    When I press FileBank action "Import materials"
    And I press "Save changes"
    Then I should see "renamed-lesson.txt"
    And "//a[normalize-space()='lesson.txt']" "xpath_element" should not exist
