@local @local_multipicker @javascript
Feature: Import mixed materials inside the native folder picker
  As a destination course teacher
  I need a visual batch selection that preserves native saving and cancellation

  Background:
    Given the following "local_multipicker > folder fixtures" exist:
      | name              |
      | Folder acceptance |
    And I am on the "Target materials" "folder activity" page logged in as "cbteacher"
    And I press "Edit"
    And I open the native MultiPicker

  Scenario: Import two folders and a resource and save with the native content form
    Then the MultiPicker module filter should be "folder"
    When I set MultiPicker filter "module" to ""
    And I capture the MultiPicker browser
    And I select MultiPicker material "Published materials" from "CB2024"
    And I select MultiPicker material "Published materials" from "CB2025"
    And I select MultiPicker material "resource.txt" from "CB2025"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "notes2024.txt"
    And the MultiPicker review should include "notes2025.txt"
    And the MultiPicker review should include "Original teacher"
    And I capture the MultiPicker browser
    When I press MultiPicker action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "existing.txt"
    And I should see "lesson.txt"
    And I should see "resource.txt"
    And I should see "notes2024.txt"
    And I should see "notes2025.txt"

  Scenario: Cancel retains the previously saved folder
    When I select MultiPicker material "Published materials" from "CB2025"
    And I press MultiPicker action "Review selection"
    And I press MultiPicker action "Import reviewed materials"
    And I press "Cancel"
    Then I should see "existing.txt"
    And I should not see "lesson.txt"

  Scenario: A content-only teacher imports without opening activity settings
    Given the MultiPicker teacher can only manage folder content
    When I select MultiPicker material "Published materials" from "CB2025"
    And I press MultiPicker action "Review selection"
    And I press MultiPicker action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "lesson.txt"
    And I should see "notes2025.txt"

  Scenario: Remove a selected folder after filtering its card out of the page
    When I select MultiPicker material "Published materials" from "CB2024"
    And I select MultiPicker material "Published materials" from "CB2025"
    And I set MultiPicker filter "q" to "resource"
    And I remove MultiPicker selected material from "CB2024"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "notes2025.txt"
    And the MultiPicker browser should not include "notes2024.txt"
    And I capture the MultiPicker browser
    When I press MultiPicker action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "notes2025.txt"
    And I should not see "notes2024.txt"

  Scenario: Review and return retain checks and functional list layout
    When I select MultiPicker material "Published materials" from "CB2025"
    And I switch MultiPicker layout to "list"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "notes2025.txt"
    When I return to MultiPicker selection
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "notes2025.txt"

  Scenario: Public assignment handouts are reusable through the normal folder picker
    When I set MultiPicker filter "module" to "assign"
    Then I should see "1" MultiPicker file cards named "handout.txt"
    And the MultiPicker browser should not include "student-private.txt"
    When I select MultiPicker material "handout.txt" from "CB2025"
    And I press MultiPicker action "Review selection"
    Then the MultiPicker review should include "handout.txt"
    When I press MultiPicker action "Import reviewed materials"
    And I press "Save changes"
    Then I should see "handout.txt"
